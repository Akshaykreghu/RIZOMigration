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
class ProjectExpensesController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'ProjectExpenses';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials', 'ExpenseTypePaymentDetails','EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Units', 'EmployeeExpenses', 'ExpenseTypeDetails','CompanyContactInfo');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index() {
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

    public function listemployees() {
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
    public function form() {
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
        if ($user_group != 2) {//This is admin side code
            $emp_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1');
            $this->set("arr_employees", $emp_list);
        } else {//This is employee side code
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->set("cur_emp_key", $cur_emp_key);

            $arr_company = $this->EmployeeDetails->find('first', array(
                'fields' => 'company_code',
                'conditions' => array(
                    'status' => 1,
                    'emp_pkey' => $cur_emp_key
                )
                    )
            );
            $company_code = $arr_company['EmployeeDetails']['company_code'];
//            $emp_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1' . $conditions);
            $apr_list_array = $this->EmployeeDetails->query("select leave_auth_apr_person_fn('" . $company_code . "'," . $cur_emp_key . ",'auth') as resps");
            $apr_keys = $apr_list_array[0][0]["resps"];
            $emp_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_pkey in (' . $apr_keys . ')');
            $this->set("apr_employees", $emp_list);
        }

        $expense_type_array = $this->EmployeeDetails->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name` FROM `expense_type` WHERE status=1");
        $this->set("expense_type", $expense_type_array);

        $vendor_array = $this->EmployeeDetails->query("SELECT `site_pkey`,`site_name`,`site_id` FROM `site` WHERE `status` = '1'");
        $this->set("vendor_list", $vendor_array);

        $data['emp_expenses_pkey'] = 0;
        $data['emp_fkey'] = '';
        $data['expenses_amount'] = "";
        $data['affected_month'] = "";
        $data['remarks'] = "";
        $data['is_credited'] = "";
        if (isset($_REQUEST['emp_expenses_pkey']) && $_REQUEST['emp_expenses_pkey'] != 0) {
            $data_db = $this->EmployeeExpenses->find("first", array("conditions" => array("emp_expenses_pkey" => $_REQUEST['emp_expenses_pkey'])));
            $data = $data_db['EmployeeExpenses'];
        }
        //  debug($data);
        $this->layout = null;
        $this->set("data", $data);
    }

//checking salary slip
    public function salarycheck() {
        $arr_request = $this->request->data;
        // debug($arr_request);
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
//        debug($arr_request);
        $empfkey = $arr_request['emp_id'];
        $form_month = $arr_request['date'];
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
                $arr_salary_month_check = $this->EmployeeExpenses->query("select emp_salary_slip.salary_amount FROM emp_salary_slip WHERE month_year= '$set_month' AND emp_fkey= '$empfkey' ");
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
    public function employeeloansave() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        if (isset($arr_form_data['authorized_by'])) {
            $arr_form_data['expense_date'] = $arr_form_data['affected_month'];
        } else {
            $arr_form_data['authorized_date'] = date('Y-m-d');
            $arr_form_data['remarks_auth'] = $arr_form_data['remarks'];
            $arr_form_data['remarks'] = "Uploaded By Admin";
            $arr_form_data['expense_date'] = $arr_form_data['affected_month'];
            $arr_form_data['expense_status'] = "Approved";
            $msg = "Expense Approved Successfully!!!";
        }
        $result = $this->EmployeeExpenses->save($arr_form_data);
        $lastkey_array = $this->EmployeeExpenses->find('first', array('fields' => 'emp_expenses_pkey', 'order' => 'emp_expenses_pkey DESC'));
        $emp_pkey = $arr_form_data['emp_fkey'];
        $arr_id_key = $lastkey_array["EmployeeExpenses"]["emp_expenses_pkey"];
//        $dirsep = "/";
        $companycode = strtolower($this->Session->read('company_code'));
        //$target_dir = "/home/myprojectsmaster/public_html/expense/" . $companycode . "/";
		$target_dir = "/home/mypayrollmaster/public_html/project/" . $companycode . "/";
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
            if ($_FILES['image']['size'] > 1000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($_FILES['image']['tmp_name']), array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                    ), true
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
    public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        if (isset($arr_request_data['project']) && $arr_request_data['project'] != "") {
            $pro = $arr_request_data['project'];
            $conditions = " and emp_expense.vendor = '$pro'";
        } else {
            $conditions = "";
        }
        if (isset($arr_request_data['payment']) && $arr_request_data['payment'] != "") {
            $conditions2 = " and emp_expense.payment_status in (" . $arr_request_data['payment'] . ")";
        } else {
            $conditions2 = "";
        }
        $counts = $this->EmployeeExpenses->query(" SELECT COUNT(*) from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
               // . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
               // . "left join expense_type on (expense_type.expense_type_pkey = emp_expense.expense_type_fkey) "
                . "where emp_expense.status = 1 and emp_expense.expense_status='Applied' $conditions $conditions2");

        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeExpenses->query("SELECT emp_expense.*,site.site_name,site.site_id,site.site_pkey,beneficiary.company_name, "
                . "employee_info.EmpName from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                //. "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                //. "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where  emp_expense.status = 1 and emp_expense.expense_status='Applied'  $conditions $conditions2"
                . "ORDER BY emp_expenses_pkey desc "
                . "limit $limit  offset $ofst ");


        $out = array();
        foreach ($arr_att as $key => $value) {
            $id = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey']: '';
            $arr = $this->EmployeeExpenses->query("SELECT expense_type.expense_type_name "
                . "from expense_type "
                . "left join emp_expense_details on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "where  emp_expense_details.status = 1 and emp_expense_details.emp_expense_fkey = $id "
                . "ORDER BY emp_expense_details.expense_details_pkey desc limit 1 ");
                        $out['project'] = isset($value['site']['site_name']) ? $value['site']['site_name'].'-'.$value['site']['site_id'] : '';
                        $out['expense_id'] = isset($value['emp_expense']['expense_id']) ? $value['emp_expense']['expense_id'] : '';
                        $out['beneficiary'] = isset($value['beneficiary']['company_name']) ? $value['beneficiary']['company_name'] : '';
                        $out['expense_date'] = isset($value['emp_expense']['expense_date']) ? date('d-m-Y',strtotime($value['emp_expense']['expense_date'])): '';
                        $out['purpose'] = isset($value['emp_expense']['purpose']) ? $value['emp_expense']['purpose'] : '';
                        $out['remarks'] = isset($value['emp_expense']['remarks']) ? $value['emp_expense']['remarks'] : '';
                        $out['expense_type'] = isset($arr['0']['expense_type']['expense_type_name']) ? $arr['0']['expense_type']['expense_type_name'] : '';
                        $out['amount'] = isset($value['emp_expense']['expenses_amount']) ? $value['emp_expense']['expenses_amount'] : '';
                        $out['payment_status'] = isset($value['emp_expense']['payment_status']) ? $value['emp_expense']['payment_status'] : '';
                        $out['emp_expenses_pkey'] = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey'] : '';
                        $out['empname'] = isset($value['employee_info']['EmpName']) ? $value['employee_info']['EmpName'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
public function employeeverifiedlist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        if (isset($arr_request_data['emp']) && $arr_request_data['emp'] != "") {
            $conditions = " and emp_expense.expense_status in (" . $arr_request_data['emp'] . ")";
        } else {
            $conditions = "";
        }
        if (isset($arr_request_data['project']) && $arr_request_data['project'] != "") {
            $pro = $arr_request_data['project'];
            $conditions1 = " and emp_expense.vendor = '$pro'";
        } else {
            $conditions1 = "";
        }
        if (isset($arr_request_data['payment']) && $arr_request_data['payment'] != "") {
            $conditions2 = " and emp_expense.payment_status in (" . $arr_request_data['payment'] . ")";
        } else {
            $conditions2 = "";
        }
        $counts = $this->EmployeeExpenses->query(" SELECT COUNT(*) from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
               // . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
               // . "left join expense_type on (expense_type.expense_type_pkey = emp_expense.expense_type_fkey) "
                . "where emp_expense.status = 1 and emp_expense.expense_status != 'Applied' $conditions $conditions1 $conditions2");

        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeExpenses->query("SELECT emp_expense.*,site.site_name,site.site_id,site.site_pkey,beneficiary.company_name, "
                . "employee_info.EmpName from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                //. "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                //. "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where  emp_expense.status = 1 and emp_expense.expense_status != 'Applied' $conditions $conditions1 $conditions2"
                . "ORDER BY emp_expense.expense_date desc "
                . "limit $limit  offset $ofst ");


        $out = array();
        foreach ($arr_att as $key => $value) {
            $id = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey']: '';
            $arr = $this->EmployeeExpenses->query("SELECT expense_type.expense_type_name "
                . "from expense_type "
                . "left join emp_expense_details on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "where  emp_expense_details.status = 1 and emp_expense_details.emp_expense_fkey = $id "
                . "ORDER BY emp_expense_details.expense_details_pkey desc limit 1 ");
                        $out['project'] = isset($value['site']['site_name']) ? $value['site']['site_name'].'-'.$value['site']['site_id'] : '';
                        $out['expense_id'] = isset($value['emp_expense']['expense_id']) ? $value['emp_expense']['expense_id'] : '';
                        $out['beneficiary'] = isset($value['beneficiary']['company_name']) ? $value['beneficiary']['company_name'] : '';
                        $out['expense_date'] = isset($value['emp_expense']['expense_date']) ? date('d-m-Y',strtotime($value['emp_expense']['expense_date'])) : '';
                        $out['purpose'] = isset($value['emp_expense']['purpose']) ? $value['emp_expense']['purpose'] : '';
                        $out['remarks'] = isset($value['emp_expense']['remarks']) ? $value['emp_expense']['remarks'] : '';
                        $out['payment_status'] = isset($value['emp_expense']['payment_status']) ? $value['emp_expense']['payment_status'] : '';
                        $out['expense_type'] = isset($arr['0']['expense_type']['expense_type_name']) ? $arr['0']['expense_type']['expense_type_name'] : '';
                        $out['amount'] = isset($value['emp_expense']['expenses_amount']) ? $value['emp_expense']['expenses_amount'] : '';
                        $out['emp_expenses_pkey'] = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey'] : '';
                        $out['empname'] = isset($value['employee_info']['EmpName']) ? $value['employee_info']['EmpName'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    public function projectlist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        if (isset($arr_request_data['emp']) && $arr_request_data['emp'] != "") {
            $conditions = " and emp_expense.expense_status in (" . $arr_request_data['emp'] . ")";
        } else {
            $conditions = "";
        }
        if (isset($arr_request_data['project']) && $arr_request_data['project'] != "") {
            $pro = $arr_request_data['project'];
            $conditions1 = " and emp_expense.vendor = '$pro'";
        } else {
            $conditions1 = "";
        }
        if (isset($arr_request_data['payment']) && $arr_request_data['payment'] != "") {
            $conditions2 = " and emp_expense.payment_status in (" . $arr_request_data['payment'] . ")";
        } else {
            $conditions2 = "";
        }
        if (isset($arr_request_data['exp_type']) && $arr_request_data['exp_type'] != "") {
            $exp_type = $arr_request_data['exp_type'];
            $conditions3 = " and emp_expense_details.expense_type_fkey = '$exp_type'";
        } else {
            $conditions3 = "";
        }
        $counts = $this->EmployeeExpenses->query(" SELECT COUNT(*) from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
               // . "left join expense_type on (expense_type.expense_type_pkey = emp_expense.expense_type_fkey) "
                . "where emp_expense.status = 1 and emp_expense_details.return_status = 1 $conditions $conditions1 $conditions2 $conditions3 group by emp_expense.emp_expenses_pkey");

        $count = count($counts);
        $arr_att = $this->EmployeeExpenses->query("SELECT emp_expense.*,site.site_name,site.site_id,site.site_pkey,beneficiary.company_name "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                //. "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.return_status = 1 $conditions $conditions1 $conditions2 $conditions3"
                . " group by emp_expense.emp_expenses_pkey ORDER BY emp_expense.expense_date desc "
                . "limit $limit  offset $ofst ");


        $out = array();
        foreach ($arr_att as $key => $value) {
            $id = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey']: '';
            $arr = $this->EmployeeExpenses->query("SELECT expense_type.expense_type_name,sum(emp_expense_details.total) as a "
                . "from expense_type "
                . "left join emp_expense_details on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "where  emp_expense_details.status = 1 and emp_expense_details.return_status = 1 and emp_expense_details.emp_expense_fkey = $id "
                . "ORDER BY emp_expense_details.expense_details_pkey desc limit 1 ");
       
                        $out['project'] = isset($value['site']['site_name']) ? $value['site']['site_name'].'-'.$value['site']['site_id'] : '';
                        $out['expense_id'] = isset($value['emp_expense']['expense_id']) ? $value['emp_expense']['expense_id'] : '';
                        $out['beneficiary'] = isset($value['beneficiary']['company_name']) ? $value['beneficiary']['company_name'] : '';
                        $out['expense_date'] = isset($value['emp_expense']['expense_date']) ? date('d-m-Y',strtotime($value['emp_expense']['expense_date'])) : '';
                        $out['purpose'] = isset($value['emp_expense']['purpose']) ? $value['emp_expense']['purpose'] : '';
                        $out['remarks'] = isset($value['emp_expense']['remarks']) ? $value['emp_expense']['remarks'] : '';
                        $out['payment_status'] = isset($value['emp_expense']['payment_status']) ? $value['emp_expense']['payment_status'] : '';
                        $out['expense_type'] = isset($arr['0']['expense_type']['expense_type_name']) ? $arr['0']['expense_type']['expense_type_name'] : '';
                        $out['amount'] = isset($arr['0']['0']['a']) ? $arr['0']['0']['a'] : '';
                        $out['emp_expenses_pkey'] = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
//    public function employeeverifiedlist() {
//        $this->autoRender = FALSE;
//        $arr_request_data = $this->request->data;
////        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
//        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
//        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
//        $limit = $_REQUEST['rows'];
//        $page = $_REQUEST['page'];
//        $ofst = ($page - 1) * $limit;
//        $this->datatable["conditions"] = array('status' => 1);
//        $resp_att = array();
//        $resp_att["rows"] = array();
//        $emp_condition = '';
//        $branch_condition = '';
//
//        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
//            $emp = $arr_request_data['employee'];
//            $emp_condition = "and au.emp_fkey=$emp";
//        } else {
//            $emp_condition = ' ';
//        }
//        if ($branch_code != '') {
//            $branch_condition = "and ed.branch_code='$branch_code'";
//        }
//
//        if (isset($arr_request_data['emp']) && $arr_request_data['emp'] != "") {
//            $conditions = " and au.expense_status in (" . $arr_request_data['emp'] . ")";
//        } else {
//            $conditions = "";
//        }
//
//        $counts = $this->EmployeeExpenses->query(" select
//            COUNT(*)
//                            from emp_details ed
//                            INNER join emp_expense au on (ed.emp_pkey = au.emp_fkey) 
//                            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
//                            LEFT join employee_info ei on (ed.emp_pkey= ei.emp_pkey)
//                            where  au.is_credited = 'N'
//                            and au.status in (1,2) and au.expense_status not in ('Applied')
//                            $emp_condition $branch_condition $conditions ");
//
//        $count = $counts[0][0]['COUNT(*)'];
//        $arr_att = $this->EmployeeExpenses->query("select au.*,ep.emp_company_id,ei.EmpName
//                            from emp_details ed
//                            INNER join emp_expense au on (ed.emp_pkey = au.emp_fkey) 
//                            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
//                            LEFT join employee_info ei on (ed.emp_pkey= ei.emp_pkey)
//                            where  au.is_credited = 'N'
//                            and au.status in (1,2)  and au.expense_status not in ('Applied')
//                            $emp_condition $branch_condition $conditions"
//                . " ORDER BY created_date desc "
//                . "limit $limit  offset $ofst ");
//
//        // debug($arr_att);
//        $out = array();
//        foreach ($arr_att as $key => $value) {
//            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
//            $out['empid'] = isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '';
//            $out['emp_expenses_pkey'] = isset($value['au']['emp_expenses_pkey']) ? $value['au']['emp_expenses_pkey'] : '';
//            $out['expenses_amount'] = isset($value['au']['expenses_amount']) ? $value['au']['expenses_amount'] : '';
//            $out['affected_month'] = isset($value['au']['affected_month']) ? $value['au']['affected_month'] : '';
//            $out['remarks'] = isset($value['au']['remarks']) ? $value['au']['remarks'] : '';
//            $out['is_credited'] = isset($value['au']['is_credited']) ? $value['au']['is_credited'] : '';
//            $out['created_date'] = isset($value['au']['created_date']) ? $value['au']['created_date'] : '';
//            $out['created_by'] = isset($value['au']['created_by']) ? $value['au']['created_by'] : '';
//            $out['modified_by'] = isset($value['au']['modified_by']) ? $value['au']['modified_by'] : '';
//            $out['modified_date'] = isset($value['au']['modified_date']) ? $value['au']['modified_date'] : '';
//            $out['status'] = isset($value['au']['status']) ? $value['au']['status'] : '';
//            $out['expense_status'] = isset($value['au']['expense_status']) ? $value['au']['expense_status'] : '';
//
//            $resp_att["rows"][$key] = $out;
//        }
//        $resp_att["total"] = $count;
//        echo json_encode($resp_att);
//    }

    //main page tab       
    public function Expenses() {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $vendor_array = $this->EmployeeExpenses->query("SELECT `site_pkey`,`site_name`,`site_id` FROM `site` WHERE `status` = '1'");
        $this->set("vendor_list", $vendor_array);
    }
    //purchase return page
    public function Purchase() {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $vendor_array = $this->EmployeeExpenses->query("SELECT `site_pkey`,`site_name`,`site_id` FROM `site` WHERE `status` = '1'");
        $this->set("vendor_list", $vendor_array);
        $exp_array = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`,`expense_type_name` FROM `expense_type` WHERE `status` = '1'");
        $this->set("exp_list", $exp_array);
    }
//delete
    public function deleteEmployee() {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //debug($ar_ids);
            $this->EmployeeExpenses->updateAll(
                    array('EmployeeExpenses.status' => 0), array('EmployeeExpenses.emp_expenses_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

     public function delete_expense($id = 0) {
        $this->autoRender = FALSE;
        $this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
        $this->ExpenseTypePaymentDetails->useDbConfig = $this->Session->read('ds');
        $this->ExpenseTypeDetails->updateAll(
                    array('ExpenseTypeDetails.status' => 0), array('ExpenseTypeDetails.expense_details_pkey' => $id));
        $this->ExpenseTypePaymentDetails->updateAll(array('ExpenseTypePaymentDetails.status' => 0), array('ExpenseTypePaymentDetails.expense_details_fkey' => $id));
        $result['msg'] = "Deleted successfully.";
        
        echo json_encode($result);
    }
    
    public function empexpenselist() {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

        $emp_exp_count = $this->EmployeeExpenses->find('count', array(
            'conditions' => array(
                'OR' => array(
                    array('authorized_by' => $cur_emp_key, 'expense_status IN ("Applied")'),
                )
            )
                )
        );
//        debug($emp_exp_count);
        $this->set('emp_exp_count', $emp_exp_count);
        $vendor_array = $this->EmployeeExpenses->query("SELECT `site_pkey`,`site_name`,`site_id` FROM `site` WHERE `status` = '1'");
        $this->set("vendor_list", $vendor_array);
    }

    public function manageexpense($expenseId = 0) {

        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $arr_att = $this->EmployeeExpenses->query("SELECT emp_expense.*,site.site_name,site.site_id,site.site_pkey,beneficiary.company_name,expense_type.expense_type_name  "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where  emp_expense.status = 1 and emp_expense.emp_expenses_pkey = '$expenseId'"
                . "ORDER BY emp_expenses_pkey desc ");
        $this->set('arr_att', $arr_att);
        $exp_status = $this->EmployeeExpenses->query("select emp_expense.expense_status,authorized_by from emp_expense where emp_expense.emp_expenses_pkey = '$expenseId'");
        $apr = $status = $exp_status['0']['emp_expense']['authorized_by'];
        if (isset($apr) && $apr != '') {
            $apr_person = $this->EmployeeExpenses->query("select EmpName from employee_info where emp_pkey=" . $apr . "");
            $this->set('apr_person', $apr_person);
        } else {
            $this->set('apr_by_admin', "Admin");
        }
        $status = $exp_status['0']['emp_expense']['expense_status'];
        if ($status == 'Approved' || $status == 'Rejected') {
            $this->set('mode', 'view');
            $arr_expense_verified = $this->EmployeeExpenses->query("SELECT emp_expense.*,employee_info.*,emp_expense_details.expense_type_fkey  FROM `emp_expense` "
                    . "left join employee_info ON emp_expense.emp_fkey = employee_info.emp_pkey "
                    . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                    . "where emp_expense.emp_expenses_pkey = '$expenseId' and emp_expense.expense_status in ('Approved','Rejected')");
            $this->set('arr_expense', $arr_expense_verified);
        } else {
            $this->set('mode', 'edit');
            $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*,employee_info.*,emp_expense_details.expense_details_pkey,emp_expense_details.expense_type_fkey FROM `emp_expense` "
                    . "left join employee_info ON emp_expense.emp_fkey = employee_info.emp_pkey "
                    . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                    . "where emp_expense.emp_expenses_pkey = '$expenseId' "
                    . "and emp_expense.expense_status = 'Applied' group by emp_expense_details.emp_expense_fkey");
          $this->set('arr_expense', $arr_expense);
        }

        if (isset($arr_expense_verified)) {
            $site_pkey = $arr_expense_verified[0]['emp_expense']['vendor'];
            $expense_type_pkey = $arr_expense_verified[0]['emp_expense_details']['expense_type_fkey'];
            //debug($expense_type_pkey);
            if (isset($site_pkey) && $site_pkey != "") {
                $site_details = $this->EmployeeExpenses->query("SELECT `site_pkey`, `site_id`, `site_name` FROM `site` WHERE `status` = '1' and site_pkey='" . $site_pkey . "'");
            }
            if (isset($expense_type_pkey) && $expense_type_pkey != "") {
                $expense_type_details = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name`,`expense_head_fkey` FROM `expense_type` WHERE `status` = '1' and expense_type_pkey='" . $expense_type_pkey . "'");
            }
        }
        if (isset($arr_expense)) {
            $site_pkey = $arr_expense[0]['emp_expense']['vendor'];
            $expense_type_pkey = $arr_expense[0]['emp_expense_details']['expense_type_fkey'];
            //debug($expense_type_pkey);
            if (isset($site_pkey) && $site_pkey != "") {
                $site_details = $this->EmployeeExpenses->query("SELECT `site_pkey`, `site_id`, `site_name` FROM `site` WHERE `status` = '1' and site_pkey='" . $site_pkey . "'");
            }
            if (isset($expense_type_pkey) && $expense_type_pkey != "") {
                $expense_type_details = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name`,`expense_head_fkey` FROM `expense_type` WHERE `status` = '1' and expense_type_pkey='" . $expense_type_pkey . "'");
            }
        }
        if (isset($site_details) && count($site_details) > 0) {
            $this->set('project_details', $site_details);
        }
        if (isset($expense_type_details) && count($expense_type_details) > 0) {
            $this->set('expense_type', $expense_type_details);
        }
    }
    
    public function grandexpense() {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey"); //Current emp_pkey. If it is admin, there is a null value.
        $arr_form_data = $this->request->data;
        $curr_expensepkey = $arr_form_data['expensepkey'];
        $headkey = isset($arr_form_data['headkey'])?$arr_form_data['headkey'] : '0';
        $currentdate = date("Y-m-d");
        $appremarks = isset($arr_form_data['REMARKS']) ? $arr_form_data['REMARKS'] : '';
        $company_code = $this->Session->read('company_code');
        $arr_data = array();
        $arr_exp_message = $this->EmployeeExpenses->find("first", array(
            'fields' => 'expense_status,authorized_by,remarks_auth,authorized_date',
            'conditions' => array('emp_expenses_pkey' => $curr_expensepkey)
        ));
        $apr_person = $arr_exp_message['EmployeeExpenses']['authorized_by'];

        if (isset($arr_form_data['reject']) && $arr_form_data['reject'] == '0') {
            $arr_data['EmployeeExpenses.expense_status'] = "'Approved'";
            $arr_data['EmployeeExpenses.status'] = "1";
        } else {
            $arr_data['EmployeeExpenses.expense_status'] = "'Rejected'";
            $arr_data['EmployeeExpenses.status'] = "1";
            if($company_code == 'ZWLK' && $headkey == 1 ){
                $this->EmployeeExpenses->query("UPDATE advance_payment SET status = '0' WHERE expense_fkey='$curr_expensepkey' ");
            }
        }
        if ($apr_person != $cur_emp_key && $cur_emp_key == null) {
            if (isset($arr_form_data['reject']) && $arr_form_data['reject'] == '0') {
                $arr_data['EmployeeExpenses.remarks_auth'] = "'Approved By ADMIN : " . $appremarks . "'";
            } else {
                $arr_data['EmployeeExpenses.remarks_auth'] = "'Rejected By ADMIN : " . $appremarks . "'";
            }
        } else {
            $arr_data['EmployeeExpenses.remarks_auth'] = "'$appremarks'";
        }
        $arr_data['EmployeeExpenses.authorized_date'] = "'$currentdate'";
        $this->EmployeeExpenses->updateAll(
                $arr_data, array('EmployeeExpenses.emp_expenses_pkey' => $curr_expensepkey)
        );
        $resp["success"] = true;
        if (isset($arr_form_data['reject']) && $arr_form_data['reject'] == '0') {
            $resp["message"] = 'Expense Approved Successfully!!!';
        } else {
            $resp["message"] = 'Expense Rejected Successfully!!!';
        }
        return json_encode($resp);
    }

    public function listempexpense() {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
        $cur_emp_key = $this->Session->read("emp_fkey");
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
//        $arr_count = $this->EmployeeExpenses->query("SELECT count(*) as count
//                                                    FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status = 'Applied' order by created_date desc");
//        $count = (int) $arr_count[0][0]['count'];
//        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(first_name,' ',last_name) AS emp_name
//                                                    FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status = 'Applied' order by created_date desc limit $limit offset $ofst");
//        $arr_expensedata["rows"] = array();
//        foreach ($arr_expense as $key => $value) {
//            $arr_expensedata["rows"][$key] = array_merge($value['emp_expense'], $value['0']);
//        }
         if (isset($arr_request_data['project']) && $arr_request_data['project'] != "") {
            $pro = $arr_request_data['project'];
            $conditions = " and emp_expense.vendor = '$pro'";
        } else {
            $conditions = "";
        }
         $counts = $this->EmployeeExpenses->query(" SELECT COUNT(*) from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where emp_expense.status = 1 and emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status='Applied' $conditions group by emp_expenses_pkey");

        $count = count($counts);
        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*,site.site_name,site.site_id,site.site_pkey,beneficiary.company_name "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status='Applied' $conditions group by emp_expenses_pkey "
                . "ORDER BY emp_expenses_pkey desc  "
                . "limit $limit  offset $ofst ");
        
        
        $arr_expensedata["rows"] = array();
        foreach ($arr_expense as $key => $value) {
            $out['expense_status'] = isset($value['emp_expense']['expense_status']) ? $value['emp_expense']['expense_status'] : '';
            $out['project'] = isset($value['site']['site_name']) ? $value['site']['site_name'].'-'.$value['site']['site_id'] : '';
            $out['expense_id'] = isset($value['emp_expense']['expense_id']) ? $value['emp_expense']['expense_id'] : '';
            $out['beneficiary'] = isset($value['beneficiary']['company_name']) ? $value['beneficiary']['company_name'] : '';
            $out['expense_date'] = isset($value['emp_expense']['expense_date']) ? $value['emp_expense']['expense_date'] : '';
            $out['purpose'] = isset($value['emp_expense']['purpose']) ? $value['emp_expense']['purpose'] : '';
            $out['remarks'] = isset($value['emp_expense']['remarks']) ? $value['emp_expense']['remarks'] : '';
            $out['emp_expenses_pkey'] = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey'] : '';
            $arr_expensedata["rows"][$key] = $out;
        }
        $arr_expensedata["total"] = (int) $count;
        echo json_encode($arr_expensedata);
    }

    public function listempexpenseverified() {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
        $cur_emp_key = $this->Session->read("emp_fkey");
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        if (isset($arr_request_data['emp']) && $arr_request_data['emp'] != "") {
            $conditions = " and emp_expense.expense_status in (" . $arr_request_data['emp'] . ")";
        } else {
            $conditions = "";
        }
//        $count_array = $this->EmployeeExpenses->query("SELECT count(*) as count
//                                                    FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status not in ('Applied') " . $conditions . " order by created_date desc");
//
//        $count = (int) $count_array[0][0]['count'];
//        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(first_name,' ',last_name) AS emp_name
//                                                    FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status not in ('Applied') " . $conditions . " order by created_date desc limit $limit offset $ofst");
////         debug($arr_expense);
//
//        $arr_expenseverifieddata["rows"] = array();
//        foreach ($arr_expense as $key => $value) {
//            $arr_expenseverifieddata["rows"][$key] = array_merge($value['emp_expense'], $value['0']);
//        }
        if (isset($arr_request_data['project']) && $arr_request_data['project'] != "") {
            $pro = $arr_request_data['project'];
            $conditions1 = " and emp_expense.vendor = '$pro'";
        } else {
            $conditions1 = "";
        }
         $counts = $this->EmployeeExpenses->query(" SELECT COUNT(*) from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where emp_expense.status = 1 and emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status not in ('Applied') $conditions $conditions1 group by emp_expenses_pkey");

        $count = count($counts);
        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*,site.site_name,site.site_id,site.site_pkey,beneficiary.company_name "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status not in ('Applied') $conditions $conditions1 group by emp_expenses_pkey "
                . "ORDER BY emp_expenses_pkey desc  "
                . "limit $limit  offset $ofst ");
        $arr_expenseverifieddata["rows"] = array(); 
        foreach ($arr_expense as $key => $value) {
            $out['expense_status'] = isset($value['emp_expense']['expense_status']) ? $value['emp_expense']['expense_status'] : '';
            $out['project'] = isset($value['site']['site_name']) ? $value['site']['site_name'].'-'.$value['site']['site_id'] : '';
            $out['expense_id'] = isset($value['emp_expense']['expense_id']) ? $value['emp_expense']['expense_id'] : '';
            $out['beneficiary'] = isset($value['beneficiary']['company_name']) ? $value['beneficiary']['company_name'] : '';
            $out['expense_date'] = isset($value['emp_expense']['expense_date']) ? $value['emp_expense']['expense_date'] : '';
            $out['purpose'] = isset($value['emp_expense']['purpose']) ? $value['emp_expense']['purpose'] : '';
            $out['remarks'] = isset($value['emp_expense']['remarks']) ? $value['emp_expense']['remarks'] : '';
            $out['emp_expenses_pkey'] = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey'] : '';
            $arr_expenseverifieddata["rows"][$key] = $out;
        }
       
        $arr_expenseverifieddata["total"] = (int) $count;
        echo json_encode($arr_expenseverifieddata);
    }

    public function employeerequests() {

        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
    }

    public function viewrequest() {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
//        $count_array = $this->EmployeeExpenses->query("SELECT count(*) as count
//                                                        FROM `emp_expense` left join emp_details ON emp_expense.authorized_by = emp_details.emp_pkey 
//                                                        where emp_expense.emp_fkey = '$cur_emp_key'");
//        $count = $count_array[0][0]['count'];
//        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(first_name,' ',last_name) AS emp_name
//                                                        FROM `emp_expense` left join emp_details ON emp_expense.authorized_by = emp_details.emp_pkey 
//                                                        where emp_expense.emp_fkey = '$cur_emp_key' order by created_date desc limit $limit offset $ofst");
        $counts = $this->EmployeeExpenses->query(" SELECT COUNT(*) from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
               // . "left join expense_type on (expense_type.expense_type_pkey = emp_expense.expense_type_fkey) "
                . "where emp_expense.status = 1 and emp_expense.emp_fkey = '$cur_emp_key' group by emp_expenses_pkey");

       // $count = $counts[0][0]['COUNT(*)'];
        $count = count($counts);
        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*,site.site_name,site.site_id,site.site_pkey,beneficiary.company_name "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
               // . "left join expense_type on (expense_type.expense_type_pkey = emp_expense.expense_type_fkey) "
                . "where  emp_expense.status = 1 and emp_expense.emp_fkey = '$cur_emp_key' group by emp_expenses_pkey "
                . "ORDER BY emp_expenses_pkey desc  "
                . "limit $limit  offset $ofst ");
        
        
        $arr_expensedata["rows"] = array();
        foreach ($arr_expense as $key => $value) {
//            if ($value['0']['emp_name'] == null) {
//                $value['0']['emp_name'] = "Admin";
//            }
//            $arr_expensedata["rows"][$key] = array_merge($value['emp_expense'], $value['0']);
           // $arr_expensedata["rows"][$key] = array_merge($value['emp_expense'], $value['site'],$value['beneficiary']);
            $out['expense_status'] = isset($value['emp_expense']['expense_status']) ? $value['emp_expense']['expense_status'] : '';
            $out['project'] = isset($value['site']['site_name']) ? $value['site']['site_name'].'-'.$value['site']['site_id'] : '';
            $out['expense_id'] = isset($value['emp_expense']['expense_id']) ? $value['emp_expense']['expense_id'] : '';
            $out['beneficiary'] = isset($value['beneficiary']['company_name']) ? $value['beneficiary']['company_name'] : '';
            $out['expense_date'] = isset($value['emp_expense']['expense_date']) ? $value['emp_expense']['expense_date'] : '';
            //$out['purpose'] = isset($value['emp_expense']['purpose']) ? $value['emp_expense']['purpose'] : '';
            $out['remarks'] = isset($value['emp_expense']['remarks']) ? $value['emp_expense']['remarks'] : '';
            $out['emp_expenses_pkey'] = isset($value['emp_expense']['emp_expenses_pkey']) ? $value['emp_expense']['emp_expenses_pkey'] : '';
            $arr_expensedata["rows"][$key] = $out;
        }
        $arr_expensedata["total"] = (int) $count;
       
        echo json_encode($arr_expensedata);
    }

    public function viewexpense($expenseId = 0) {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*,employee_info.*
                                                        FROM `emp_expense` left join employee_info ON emp_expense.emp_fkey = employee_info.emp_pkey 
                                                        where emp_expense.emp_fkey = '$cur_emp_key' and emp_expense.emp_expenses_pkey = '$expenseId' ");
        $this->set('arr_expense', $arr_expense);
        $apr = $arr_expense[0]['emp_expense']['authorized_by'];
        if (isset($apr) && $apr != NULL) {
            $apr_person = $this->EmployeeExpenses->query("select EmpName from employee_info where emp_pkey=" . $apr . "");
            $this->set('apr_person', $apr_person);
        } else {
            $this->set('apr_by_admin', "Admin");
        }
        
        if (isset($arr_expense)) {
//            debug($arr_expense);
            $site_pkey = $arr_expense[0]['emp_expense']['vendor'];
            $expense_type_pkey = $arr_expense[0]['emp_expense']['expense_type'];
            if (isset($site_pkey) && $site_pkey != "") {
                $site_details = $this->EmployeeExpenses->query("SELECT `site_pkey`, `site_id`, `site_name` FROM `site` WHERE `status` = '1' and site_pkey='" . $site_pkey . "'");
            }
            if (isset($expense_type_pkey) && $expense_type_pkey != "") {
                $expense_type_details = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name` FROM `expense_type` WHERE `status` = '1' and expense_type_pkey='" . $expense_type_pkey . "'");
            }
        }
        if (isset($site_details) && count($site_details) > 0) {
//            debug($site_details);
            $this->set('project_details', $site_details);
        }
        if (isset($expense_type_details) && count($expense_type_details) > 0) {
//            debug($expense_type_details);
            $this->set('expense_type', $expense_type_details);
        }
//        debug($arr_expense);
    }

    public function showimage($pkey = 0) {
        $this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
        $str_company_code = $this->Session->read('company_code');
        $this->set('company_code', $str_company_code);
        //$image = $this->EmployeeExpenses->query("select emp_expense.image from emp_expense where emp_expense.emp_expenses_pkey = '$pkey'");
        $image = $this->ExpenseTypeDetails->query("select image from emp_expense_details where expense_details_pkey = '$pkey'");
        $this->set('image', $image);
    }
    public function advanceamount($pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        if($pkey == 0){
            $pkey = $this->Session->read("emp_fkey");
        }
        if($company_code == 'ZWLK' || $company_code == 'DEMO'){
        $dates = date('Y-m-d');
            $adv_array = $this->EmployeeExpenses->query("select sum(amount) as amt from advance_expense where status=1 and emp_fkey = '$pkey' and advance_date <= '$dates'");
            $pay_array = $this->EmployeeExpenses->query("select sum(amount) as amt from advance_payment 
            left join emp_expense on (emp_expense.emp_expenses_pkey = advance_payment.expense_fkey) 
            where advance_payment.status=1 and advance_payment.emp_fkey = '$pkey' and adv_date <= '$dates' 
            and emp_expense.expense_status in ('Approved','Applied')");
            $amt1 = isset($adv_array['0']['0']['amt'])?$adv_array['0']['0']['amt']:0;
            $amt2 = isset($pay_array['0']['0']['amt'])?$pay_array['0']['0']['amt']:0;
            
            $balance = $amt1 - $amt2;
            echo json_encode($balance);
        }    else{
            echo json_encode($balance);
        }
    }
    public function loadnew() {  
      //  $arr_request_data = $this->request->data;
      //  $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
      //  $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//        $user_group = $this->Session->read('user_group');
//        $cur_emp_key = $this->Session->read("emp_fkey");
//        $this->set("user_group", $user_group);
//        if ($user_group == 2) {
//            $join_condition = " JOIN allocate_expense on (allocate_expense.expense_type_fkey=expense_type.expense_type_pkey and allocate_expense.status=1) JOIN designation on (allocate_expense.designation_fkey=designation.id) JOIN emp_proff on (emp_proff.designation=designation.desig_code and emp_proff.emp_fkey='$cur_emp_key') JOIN employee_info on (emp_proff.emp_fkey=employee_info.emp_pkey) ";
//        } else {
//            $join_condition = "";
//        }
//        $expense_type_array = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_name`,`expense_head_fkey` FROM `expense_type` ".$join_condition." WHERE expense_type.status=1 order by expense_type_name asc");
//        $this->set("expense_type", $expense_type_array);
//        $vendor_array = $this->EmployeeExpenses->query("SELECT `site_pkey`,`site_name`,`site_id` FROM `site` WHERE `status` = '1' ORDER BY site_name ASC");
//        $this->set("vendor_list", $vendor_array);
//        $beneficiary = $this->EmployeeExpenses->query("SELECT `company_name`,`contact_id` FROM `beneficiary` WHERE `status` = '1' ORDER BY company_name ASC");
//        $this->set("beneficiary", $beneficiary);
//        $company_code = $this->Session->read("company_code");
//        $this->set("company_code", $company_code);
//        if ($user_group == 2) {
//        $emp_list = $this->EmployeeExpenses->query("select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_proff.emp_fkey = '$cur_emp_key' ORDER BY first_name ASC");
//        
//        $apr_keys = 0;
//        $apr_list_array = $this->EmployeeDetails->query("select leave_auth_apr_person_fn('" . $company_code . "'," . $cur_emp_key . ",'auth') as resps");
//        $apr_keys = $apr_list_array[0][0]["resps"];
//        
//        if(!empty($apr_keys)){
//        $apr_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_pkey in (' . $apr_keys . ') ORDER BY first_name ASC');
//        }else{
//        $apr_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 ORDER BY first_name ASC');
//        }
//        $this->set("apr_employees", $apr_list);
//        $this->set("arr_employees", $emp_list);
//        if($company_code == 'ZWLK' || $company_code == 'DEMO'){
//        $dates = date('Y-m-d');
//            $adv_array = $this->EmployeeExpenses->query("select sum(amount) as amt from advance_expense where status=1 and emp_fkey = '$cur_emp_key' and advance_date <= '$dates'");
//            $pay_array = $this->EmployeeExpenses->query("select sum(amount) as amt from advance_payment where status=1 and emp_fkey = '$cur_emp_key' and adv_date <= '$dates'");
//            $amt1 = isset($adv_array['0']['0']['amt'])?$adv_array['0']['0']['amt']:0;
//            $amt2 = isset($pay_array['0']['0']['amt'])?$pay_array['0']['0']['amt']:0;
//            
//            $balance = $amt1 - $amt2;
//            $this->set("arr_balance", $balance);
//        }    else{
//            $this->set("arr_balance", 0);
//        }
//         }
//         else{
       // $emp_list = $this->EmployeeExpenses->query("select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 ORDER BY first_name ASC");
       // }
        
//        $pkey = isset($_REQUEST['emp_expenses_pkey'])?$_REQUEST['emp_expenses_pkey']:'';
//        if($pkey){
//        $where = " where emp_expense.expense_type = '$pkey'";
//        }
//        else{
//        $where = "";
//        } 
//        $arr_data = array();
//       if (isset($_REQUEST['emp_expenses_pkey']) && $_REQUEST['emp_expenses_pkey'] != 0) {
//        $arr_data = $this->EmployeeExpenses->query("SELECT emp_expense.*,emp_expense_details.*,expense_type.expense_type_pkey,site.site_name,site.site_id,site.site_pkey"
//                . " from emp_expense "
//                . "left join site on (site.site_pkey = emp_expense.vendor) "
//                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.expense_details_fkey) "
//                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
//                . "$where");
//       $this->set("data", $arr_data);
//        
//       }
 
        $this->render('newexpense');
    }
    public function aprlist() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        if ($q != null) {
            $q_condition = " and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%') ";
        } else {
            $q_condition = "";
        }
        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $company_code = $this->Session->read("company_code");
        if ($user_group == 2) {
        $apr_keys = 0;
        $apr_list_array = $this->EmployeeDetails->query("select leave_auth_apr_person_fn('" . $company_code . "'," . $cur_emp_key . ",'auth') as resps");
        $apr_keys = $apr_list_array[0][0]["resps"];
        
        if(!empty($apr_keys)){
        $branch_array = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_pkey in (' . $apr_keys . ') '. $q_condition .' ORDER BY first_name ASC');
        }else{
        $branch_array = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 '. $q_condition .' ORDER BY first_name ASC');
        }
        }
        
        $array = array();
        $branch = array();
        //$branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
     public function emplist() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        if ($q != null) {
            $q_condition = " and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%') ";
        } else {
            $q_condition = "";
        }
        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $company_code = $this->Session->read("company_code");
        if ($user_group == 2) {
        $branch_array = $this->EmployeeDetails->query("select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 $q_condition and emp_proff.emp_fkey = '$cur_emp_key' ORDER BY first_name ASC");
        }
        else{
        $branch_array = $this->EmployeeDetails->query("select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 $q_condition ORDER BY first_name ASC");
        }
        
        $array = array();
        $branch = array();
        //$branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    public function explist() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        if ($q != null) {
            $q_condition = " and expense_type_name like '%$q%' ";
        } else {
            $q_condition = "";
        }
        
        $user_group = $this->Session->read('user_group');
        
        if ($user_group == 2) {
            $join_condition = " JOIN allocate_expense on (allocate_expense.expense_type_fkey=expense_type.expense_type_pkey and allocate_expense.status=1) JOIN designation on (allocate_expense.designation_fkey=designation.id) JOIN emp_proff on (emp_proff.designation=designation.desig_code and emp_proff.emp_fkey='$cur_emp_key') JOIN employee_info on (emp_proff.emp_fkey=employee_info.emp_pkey) ";
        } else {
            $join_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("SELECT `expense_type_pkey`, `expense_type_name`,`expense_head_fkey` FROM `expense_type` ".$join_condition." WHERE expense_type.status=1 $q_condition order by expense_type_name asc");
        
        $array = array();
        $branch = array();
        //$branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['expense_type']['expense_type_pkey'],
                'text' => $value['expense_type']['expense_type_name'] 
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    public function benlist() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        if ($q != null) {
            $q_condition = " and  beneficiary.company_name like '%$q%' ";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("SELECT `company_name`,`contact_id` FROM `beneficiary` WHERE `status` = '1' $q_condition ORDER BY company_name ASC");
        
        $array = array();
        $branch = array();
        //$branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['beneficiary']['contact_id'],
                'text' => $value['beneficiary']['company_name'] 
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    public function prolist() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her site data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $temp_site_keys2 = $this->EmployeeDetails->query("select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and status=1");
            $site_key = array();
            foreach ($temp_site_keys2 as $val) {
                $site_key[] = $val['access_site']['site_fkey'];
            }
            if (count($site_key) > 0) {
                $site_condition = " and site_pkey in (" . implode(',', $site_key) . ") ";
            } else {
                $site_condition = " and site_pkey = 0 ";
            }
        } else {
            $site_condition = "";
        }
        
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        if ($q != null) {
            $q_condition = " and site.site_name like '%$q%' ";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("SELECT `site_pkey`,`site_name`,`site_id` FROM `site` WHERE `status` = '1' $q_condition $site_condition ORDER BY site_name ASC");
        $array = array();
        $branch = array();
        //$branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['site']['site_pkey'],
                'text' => $value['site']['site_name'] . ' - ' . $value['site']['site_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
     public function editexpense($id) {
        $arr_request_data = $this->request->data;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $expense_type_array = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name` FROM `expense_type` WHERE status=1 order by expense_type_name asc");
        $this->set("expense_type", $expense_type_array);
        
        $emp_list = $this->EmployeeExpenses->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 ORDER BY first_name ASC');
        $this->set("arr_employees", $emp_list);
        
        $exp_details = $this->EmployeeExpenses->query("SELECT emp_expense_details.*,expense_item.category,expense_type.expense_type_name "
                . "from emp_expense_details "
                . "left join emp_expense on (emp_expense.emp_expenses_pkey = emp_expense_details.emp_expense_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where emp_expense_details.expense_details_pkey = '$id' and emp_expense_details.status = 1");
       
        $this->set("data", $exp_details);
        $results= $this->EmployeeExpenses->query("SELECT expense_date from emp_expense_payment where `expense_details_fkey` = '$id' and status=1 order by payment_pkey desc limit 1 ");
        $date = $results['0']['emp_expense_payment']['expense_date'];
        $this->set("date",$date);
    }
    public function returnexpense($id,$key) {
        $arr_request_data = $this->request->data;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $expense_type_array = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name` FROM `expense_type` WHERE status=1 order by expense_type_name asc");
        $this->set("expense_type", $expense_type_array);
        
        $emp_list = $this->EmployeeExpenses->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 ORDER BY first_name ASC');
        $this->set("arr_employees", $emp_list);
        
        $exp_details = $this->EmployeeExpenses->query("SELECT emp_expense_details.*,expense_item.category,expense_type.expense_type_name "
                . "from emp_expense_details "
                . "left join emp_expense on (emp_expense.emp_expenses_pkey = emp_expense_details.emp_expense_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where emp_expense_details.expense_details_pkey = '$id' and emp_expense_details.status = 1");
        $exp_details1 = $this->EmployeeExpenses->query("SELECT sum(emp_expense_details.exp_amount)as a,sum(emp_expense_details.cgst)as b,sum(emp_expense_details.sgst)as c,sum(emp_expense_details.igst)as d,sum(emp_expense_details.total) as e  "
                . "from emp_expense_details "
                . "where emp_expense_details.emp_expense_fkey = '$key' and emp_expense_details.status = 1");
        $this->set("sum", $exp_details1);
        $this->set("data", $exp_details);
        $results= $this->EmployeeExpenses->query("SELECT expense_date from emp_expense_payment where `expense_details_fkey` = '$id' and status=1 order by payment_pkey desc limit 1 ");
        $date = $results['0']['emp_expense_payment']['expense_date'];
        $this->set("date",$date);
    }
    
      public function editexpensepayment($id) {
        $arr_request_data = $this->request->data;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $expense_type_array = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name` FROM `expense_type` WHERE status=1 order by expense_type_name asc");
        $this->set("expense_type", $expense_type_array);
        $vendor_array = $this->EmployeeExpenses->query("SELECT `site_pkey`,`site_name`,`site_id` FROM `site` WHERE `status` = '1' order by site_name asc");
        $this->set("vendor_list", $vendor_array);
        $beneficiary = $this->EmployeeExpenses->query("SELECT `company_name`,`contact_id` FROM `beneficiary` WHERE `status` = '1' order by company_name asc");
        $this->set("beneficiary", $beneficiary);
        $emp_list = $this->EmployeeExpenses->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 order by first_name asc');
        $this->set("arr_employees", $emp_list);
        $total = $this->EmployeeExpenses->query("SELECT sum(total),sum(payment),sum(balance) from emp_expense_details where `emp_expense_fkey` = '$id' and status=1");
        $sum = $total['0']['0']['sum(total)'];
        $payment = $total['0']['0']['sum(payment)'];
        $balance = $total['0']['0']['sum(balance)'];
        $this->set("sum", $sum);
        $this->set("payment", $payment);
        $this->set("balance", $balance);
        $exp_details = $this->EmployeeExpenses->query("SELECT emp_expense.*,emp_details.first_name,emp_details.last_name,site.site_name,site.site_id,site.site_pkey "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "where emp_expense.emp_expenses_pkey = '$id' and emp_expense.status = 1");
       
        $this->set("data", $exp_details);
        $user_group = $this->Session->read('user_group');
        $this->set("user_group", $user_group);
        $this->render('editexpensepayment');
    }
    //form save
    public function save() {
        $this->autoRender = FALSE;
        $this->layout = null;
	$this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
	$this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
        $this->ExpenseTypePaymentDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        $user_group = $this->Session->read("user_group");
        $msg = '';
                
        if(empty($arr_form_data['emp_expenses_pkey'])){
            $arr_form_data['status'] = 0;
            $arr_form_data['authorized_date'] = date('Y-m-d');
            $arr_form_data['remarks_auth'] = $arr_form_data['remarks'];
            $arr_form_data['remarks'] = isset($arr_form_data['remarks'])?$arr_form_data['remarks']:"";
            //$arr_form_data['expenses_amount'] = $arr_form_data['exp_total'];
            //$arr_form_data['expense_date'] = $arr_form_data['affected_month'];
             
            if ($user_group == '1') {
            $arr_form_data['authorized_by'] = "Admin";
            $arr_form_data['expense_status'] = "Approved";
            $msg = "Expense Approved Successfully!!!";
            $arr_form_data['emp_fkey'] = isset($arr_form_data['emp_fkey'])?$arr_form_data['emp_fkey']:'';
            }else
            if ($user_group == '2') {
            $arr_form_data['authorized_by'] = $arr_form_data['authorized_by'];
            $arr_form_data['expense_status'] = "Applied";
            $arr_form_data['emp_fkey'] = $this->Session->read("emp_fkey"); 
            }
       
        
        //$arr_form_data['purpose'] = isset($arr_form_data['purpose'])?$arr_form_data['purpose']:'';
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $arr_form_data['beneficiary_fkey'] = isset($arr_form_data['beneficiary'])?$arr_form_data['beneficiary']:'';
        $this->EmployeeExpenses->save($arr_form_data);
        }
        else{
        $key = $arr_form_data['emp_expenses_pkey'];
        if($arr_form_data['vendor']){
        $arr_form['vendor'] = isset($arr_form_data['vendor'])?$arr_form_data['vendor']:0;
        }
        
        $arr_form['beneficiary_fkey'] = isset($arr_form_data['beneficiary'])?$arr_form_data['beneficiary']:0;
        
        if($arr_form_data['expense_date'] != '0000-00-00'){
        $arr_form['expense_date'] = "'".$arr_form_data['expense_date']."'";
        }
        if($arr_form_data['remarks']){
        $arr_form['remarks'] = "'".$arr_form_data['remarks']."'";
        }
        if($arr_form_data['gst_bill_no']){
        $arr_form['gst_bill_no'] = "'".$arr_form_data['gst_bill_no']."'";
        }
        if($arr_form_data['gst_bill_status']){
        $arr_form['gst_bill_status'] = "'".$arr_form_data['gst_bill_status']."'";
        }
//        if($arr_form_data['exp_total']){
//        $arr_form['expenses_amount'] = $arr_form_data['exp_total'];
//        }
        if($arr_form_data['payment']){
        $arr_form['payment'] = $arr_form_data['payment'];
        }
        if($arr_form_data['balance']){
        $arr_form['balance'] = $arr_form_data['balance'];
        }
        if($arr_form_data['payment_status']){
        $arr_form['payment_status'] = "'".$arr_form_data['payment_status']."'";
        }
        $auth = isset($arr_form_data['authorized_by'])?$arr_form_data['authorized_by']:'';
        if($auth){
        $arr_form['authorized_by'] = $arr_form_data['authorized_by'];
        }
        $this->EmployeeExpenses->updateAll($arr_form, array('emp_expenses_pkey' => $key));
        }
        $getlastid = isset($arr_form_data['emp_expenses_pkey']) && !empty($arr_form_data['emp_expenses_pkey']) ? $arr_form_data['emp_expenses_pkey'] : $this->EmployeeExpenses->getInsertid();
        
        $emp = isset($arr_form_data['emp_fkey'])?$this->Session->read('user_name'):'';
        $arr_data['status'] = 0;
        $arr_data['emp_expense_fkey'] = $getlastid;
        $arr_data['expense_type_fkey'] = isset($arr_form_data['expense_type'])?$arr_form_data['expense_type']:0;
        $arr_data['category_fkey'] = isset($arr_form_data['category'])?$arr_form_data['category']:0;
        //$emp = $arr_data['emp_fkey'] = $arr_form_data['emp_fkey'];
//        if($arr_form_data['emp_expense_date']){
//        $arr_data['exp_date'] = $arr_form_data['emp_expense_date'];
//        }else{
        $arr_data['exp_date'] = $arr_form_data['expense_date'];
        //}
        $arr_data['exp_amount'] = $arr_form_data['expense_amount'];
        $arr_data['cgst'] = $arr_form_data['cgst'];
        $arr_data['sgst'] = $arr_form_data['sgst'];
        $arr_data['igst'] = $arr_form_data['igst'];
        $arr_data['total'] = $arr_form_data['total'];
//        $arr_data['gst_bill_no'] = $arr_form_data['gst_bill_no'];
//        $arr_data['gst_bill_status'] = $arr_form_data['gst_bill_status'];
         if($arr_form_data['exp_payment'] == ""){
           $arr_data['payment'] = 0;
         }else{
           $arr_data['payment'] = $arr_form_data['exp_payment'];
         }
        $arr_data['balance'] = $arr_form_data['exp_balance'];
         if($arr_form_data['exp_balance'] > 0){
          $arr_data['payment_status'] = "Pending";   
         }else{
          $arr_data['payment_status'] = "Completed";   
         }
        $arr_data['related_party'] = $arr_form_data['related_party'];
        $arr_data['created_by'] = $this->Session->read('user_name');
        $this->ExpenseTypeDetails->saveAll($arr_data);
        $insert_id = $this->ExpenseTypeDetails->getInsertid();
        $arr_payment['expense_details_fkey'] = $insert_id;
        $arr_payment['pay_amount'] = isset($arr_data['payment'])?$arr_data['payment']:0;
        $arr_payment['balance'] = $arr_form_data['exp_balance'];
        $arr_payment['expense_date'] = $arr_form_data['expense_date'];
        $arr_payment['created_by'] = $this->Session->read('user_name');
        $this->ExpenseTypePaymentDetails->saveAll($arr_payment);
//        foreach ($allitem as $value) {
//            $arr_form_data['package'] = isset($value['itemdetails']['package'])?$value['itemdetails']['package']:0;
//            $arr_form_data['re_order_level'] = $value['qtydetails']['re_order_level'];
//        }
//        if($this->CheckIfExists($getlastid,$itemid)){
//            //debug("available");
//            $this->UpdateIfExists($getlastid,$itemid,$arr_form_data['required_qty']);
//            echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $getlastid));
//            return;
//        }else{
//            //debug("Not available");
//        }
        $companycode = strtolower($this->Session->read('company_code'));
        //$target_dir = "/home/myprojectsmaster/public_html/expense/" . $companycode . "/";
        $target_dir = "/home/mypayrollmaster/public_html/project/" . $companycode . "/";
        $arr_form_data2 = array();
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
            if ($_FILES['image']['size'] > 1000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($_FILES['image']['tmp_name']), array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                    ), true
                    )) {
                throw new RuntimeException('Invalid file format.');
            }

//            $filename = sprintf('%s.%s', sha1_file($_FILES['image']['tmp_name']), $ext);
            $filename = $getlastid . '_' . $insert_id . '_' . basename($_FILES["image"]["name"]);

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            $arr_form_data2["image"] = "'" . $filename . "'";
//                $this->Session->write('company_logo', $file_webroot_path . $filename);
        } catch (RuntimeException $e) {
            
        }
//        $file_webroot_path = "files/companylogos/". $companycode ."/";
//        $this->EmployeeExpenses->id($arr_id_key);
        if (count($arr_form_data2) > 0) {
            $result = $this->ExpenseTypeDetails->updateAll($arr_form_data2, array('expense_details_pkey' => $insert_id));
        }
        $total = $this->EmployeeExpenses->query("SELECT sum(total) from emp_expense_details where `emp_expense_fkey` = '$getlastid' and status=0");
        $sum = $total['0']['0']['sum(total)'];
        $total_payment = $this->EmployeeExpenses->query("SELECT sum(payment) from emp_expense_details where `emp_expense_fkey` = '$getlastid' and status=0");
        $payment = $total_payment['0']['0']['sum(payment)'];
        echo json_encode(array('msg' => 'Added Project Expense Successfully', 'pk' => $getlastid,'total'=>$sum,'payment'=>$payment));
    }
    public function edit_save() {
        $this->autoRender = FALSE;
        $this->layout = null;
	$this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
	$this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
        $this->ExpenseTypePaymentDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $pkey = $arr_form_data['expense_details_pkey'];
        $pay_status = $arr_form_data['pay_status'];
        $exp_pkey = $arr_data['emp_expense_fkey'] = $arr_form_data['emp_expense_fkey'];
        $arr_data['expense_type_fkey'] = $arr_form_data['expense_type'];
        $arr_data['category_fkey'] = isset($arr_form_data['category'])?$arr_form_data['category']:0;
       
        $arr_data['exp_amount'] = $arr_form_data['expenses_amount'];
        if($arr_form_data['cgst'] > 0){
        $arr_data['cgst'] = $arr_form_data['cgst'];
        }
        if($arr_form_data['sgst'] > 0){
        $arr_data['sgst'] = $arr_form_data['sgst'];
        }
        if($arr_form_data['igst'] > 0){
        $arr_data['igst'] = $arr_form_data['igst'];
        }
        $arr_data['total'] = $arr_form_data['total'];
//        $arr_data['gst_bill_no'] = $arr_form_data['gst_bill_no'];
//        $arr_data['gst_bill_status'] = $arr_form_data['gst_bill_status'];
        $expense_data['payment'] = $arr_data['payment'] = $arr_form_data['payment'] + $arr_form_data['payment_total'];
        $expense_data['balance'] = $arr_data['balance'] = $arr_form_data['total'] - $arr_data['payment'];

        if($arr_form_data['related_party'] != ''){
        $arr_data['related_party'] = "'".$arr_form_data['related_party']."'";
        }
    
        if($arr_form_data['payment'] != ''){
        if($arr_form_data['expense_date'] == ""){
           $arr_payment['expense_date'] = date('Y-m-d');
         }else{
           $arr_payment['expense_date'] = $arr_form_data['expense_date'];
         }
        $arr_payment['expense_details_fkey'] = $pkey;
        
        $arr_payment['pay_amount'] = $arr_form_data['payment'];
        
        $arr_payment['balance'] = $arr_data['balance'];
        if($arr_payment['balance'] > 0){
          $expense_data['payment_status']="'Pending'";   
         }else{
          $expense_data['payment_status']="'Completed'";    
         }
        
        $arr_payment['created_by'] = $this->Session->read('user_name');
        if($pay_status == 'Pending'){
        $result = $this->ExpenseTypePaymentDetails->saveAll($arr_payment);
        }
        }else{
        if($pay_status == 'Completed'){
        $results= $this->EmployeeExpenses->query("SELECT payment_pkey,expense_date from emp_expense_payment where `expense_details_fkey` = '$pkey' and status=1 order by payment_pkey desc limit 1 ");
        $date = $results['0']['emp_expense_payment']['expense_date'];
     
        if($arr_form_data['expense_date'] != ''){
        $arr_payments['expense_date'] =  "'".$arr_form_data['expense_date']."'";
        }else{
        $arr_payments['expense_date'] = "'".$date."'";
        }
        $key = $results['0']['emp_expense_payment']['payment_pkey'];
        $result = $this->ExpenseTypePaymentDetails->updateAll($arr_payments, array('payment_pkey' => $key));
        }
        }
        
        $result = $this->ExpenseTypeDetails->updateAll($expense_data, array('expense_details_pkey' => $pkey));
        
        $total5 = $this->EmployeeExpenses->query("SELECT sum(payment),sum(balance) from emp_expense_details where `emp_expense_fkey` = '$exp_pkey' and status=1");
        $expense['payment'] = round($total5['0']['0']['sum(payment)'],2);
        $expense['balance'] = round($total5['0']['0']['sum(balance)'],2); 
        if($expense['balance'] > 0){
          $expense['payment_status']="'Pending'";   
         }else{
          $expense['payment_status']="'Completed'";    
         }
        $result = $this->EmployeeExpenses->updateAll($expense, array('emp_expenses_pkey' => $exp_pkey));
        //$arr_data['image'] = isset($arr_form_data['oldimg'])?$arr_form_data['oldimg']:"''";
        $admin = $this->Session->read('user_name');
        $arr_data['modified_by'] = '"' .$admin. '"';
        $arr_data['modified_date'] = "'" .date("Y-m-d h:i:s"). "'";
        //$this->ExpenseTypeDetails->saveAll($arr_data);
        //$insert_id = $this->ExpenseTypeDetails->getInsertid();
        
        $companycode = strtolower($this->Session->read('company_code'));
        //$target_dir = "/home/myprojectsmaster/public_html/expense/" . $companycode . "/";
        $target_dir = "/home/mypayrollmaster/public_html/project/" . $companycode . "/";
        $arr_form_data2 = array();
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
            if ($_FILES['image']['size'] > 1000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($_FILES['image']['tmp_name']), array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                    ), true
                    )) {
                throw new RuntimeException('Invalid file format.');
            }

//            $filename = sprintf('%s.%s', sha1_file($_FILES['image']['tmp_name']), $ext);
            $filename = $exp_pkey . '_' . $pkey . '_' . basename($_FILES["image"]["name"]);

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            if($_FILES["image"]["name"] != '')
            $arr_data["image"] = "'" . $filename . "'";
//                $this->Session->write('company_logo', $file_webroot_path . $filename);
        } catch (RuntimeException $e) {
            
        }
//        $file_webroot_path = "files/companylogos/". $companycode ."/";
//        $this->EmployeeExpenses->id($arr_id_key);
        
        //if (count($arr_form_data2) > 0) {
            $result = $this->ExpenseTypeDetails->updateAll($arr_data, array('expense_details_pkey' => $pkey));
        //}
        echo json_encode(array('msg' => 'Updated Project Expense Successfully', 'pk' => $exp_pkey,'success'=>true));
    }
     public function return_save() {
        $this->autoRender = FALSE;
        $this->layout = null;
	$this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
	$this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
        $this->ExpenseTypePaymentDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $pkey = $arr_form_data['expense_details_pkey'];
        $pay_status = $arr_form_data['pay_status'];
        $exp_pkey = $arr_data['emp_expense_fkey'] = $arr_form_data['emp_expense_fkey'];
        $arr_data['expense_type_fkey'] = $arr_form_data['expense_type'];
        //$arr_data['category_fkey'] = isset($arr_form_data['category'])?$arr_form_data['category']:0;
        $arr_data['credit_note'] = $arr_form_data['credit_note'];
        $arr_data['credit_date'] = isset($arr_form_data['credit_date'])?$arr_form_data['credit_date']:'';
        $arr_data['ref_bill_no'] = isset($arr_form_data['ref_bill_no'])?$arr_form_data['ref_bill_no']:'';
        $arr_data['exp_amount'] = - $arr_form_data['expenses_amount'];
        if($arr_form_data['cgst'] > 0){
        $arr_data['cgst'] = - $arr_form_data['cgst'];
        }
        if($arr_form_data['sgst'] > 0){
        $arr_data['sgst'] = - $arr_form_data['sgst'];
        }
        if($arr_form_data['igst'] > 0){
        $arr_data['igst'] = - $arr_form_data['igst'];
        }
        $arr_data['total'] = - $arr_form_data['total'];
        $expense_data['payment'] = $arr_data['payment'] = 0 - $arr_form_data['payment_total'];
        $expense_data['balance'] = $arr_data['balance'] = $arr_form_data['payment_total'] - $arr_form_data['total'];

        if($arr_form_data['expense_date'] == ""){
           $arr_data['exp_date'] = $arr_payment['expense_date'] = date('Y-m-d');
         }else{
           $arr_data['exp_date'] =  $arr_form_data['expense_date'];
           $arr_payment['expense_date'] = $arr_form_data['expense_date'];
         }
        
        $arr_payment['pay_amount'] = - $arr_form_data['payment_total'];
        $arr_payment['balance'] = $arr_form_data['paid_total'] - $arr_form_data['payment_total'];
        if($arr_payment['balance'] > 0){
          $arr_data['payment_status'] = $expense_data['payment_status']="Pending";   
         }else{
          $arr_data['payment_status'] = $expense_data['payment_status']="Completed";    
         }
        $arr_data['created_by'] = $this->Session->read('user_name');
        $arr_payment['created_by'] = $this->Session->read('user_name');
        $arr_data['return_status'] = 1;
        $admin = $this->Session->read('user_name');
        $arr_data['modified_by'] = $admin;
        $arr_data['modified_date'] = "'" .date("Y-m-d h:i:s"). "'";
        $result = $this->ExpenseTypeDetails->saveAll($arr_data);
        $insert_id = $this->ExpenseTypeDetails->getInsertid();
        $arr_payment['expense_details_fkey'] = $insert_id;
        $arr_payment['status'] = 1;
        if($arr_form_data['payment_total'] !== ""){
        $result = $this->ExpenseTypePaymentDetails->saveAll($arr_payment);
        }
        //$result = $this->ExpenseTypeDetails->updateAll($expense_data, array('expense_details_pkey' => $pkey));
        
        $total5 = $this->EmployeeExpenses->query("SELECT sum(payment),sum(balance) from emp_expense_details where `emp_expense_fkey` = '$exp_pkey' and status=1");
        $expense['payment'] = round($total5['0']['0']['sum(payment)'],2);
        $expense['balance'] = round($total5['0']['0']['sum(balance)'],2); 
        if($expense['balance'] > 0){
          $expense['payment_status']="'Pending'";   
        }else{
          $expense['payment_status']="'Completed'";    
        }
        $result = $this->EmployeeExpenses->updateAll($expense, array('emp_expenses_pkey' => $exp_pkey));
        
        //$result = $this->ExpenseTypeDetails->updateAll($arr_data, array('expense_details_pkey' => $insert_id));
        echo json_encode(array('msg' => 'Credit Note Added Successfully', 'pk' => $exp_pkey,'success'=>true));
    }
     public function editpayment_save() {
        $this->autoRender = FALSE;
        $this->layout = null;
	$this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $pkey = $arr_form_data['emp_expenses_pkey'];
        //$result = $this->EmployeeExpenses->updateAll(array('status'=> 3), array('expense_details_pkey' => $pkey));
        //$arr_data['status'] = 1;
        
        $emp = $arr_data['emp_fkey'] = isset($arr_form_data['emp_fkey'])?"'".$arr_form_data['emp_fkey']."'":$this->Session->read('emp_fkey');
        $arr_data['vendor'] = "'".$arr_form_data['vendor']."'";
        $arr_data['beneficiary_fkey'] = "'".$arr_form_data['beneficiary_fkey']."'";
        $arr_data['gst_bill_no'] = "'".$arr_form_data['gst_bill_no']."'";
        $arr_data['gst_bill_status'] = "'".$arr_form_data['gst_bill_status']."'";
        $arr_data['payment'] = "'".$arr_form_data['payment']."'";
        $arr_data['balance'] = $arr_form_data['balance'];
        $arr_data['payment_status'] = "'".$arr_form_data['payment_status']."'";
        $arr_data['expense_date'] = "'".$arr_form_data['expense_date']."'";
        $arr_data['modified_by'] = "'".$this->Session->read('user_name')."'";
        $arr_data['modified_date'] = "'".date("Y-m-d h:i:s")."'";
        $arr_data['remarks'] = "'".$arr_form_data['remarks']."'";
        $this->EmployeeExpenses->updateAll($arr_data, array('emp_expenses_pkey' => $pkey));
        echo json_encode(array('msg' => 'Updated Project Details Successfully', 'pk' => $pkey,'success'=>true));
    }
  public function loadtable($id, $rowindex = 1) {
        $this->autoRender = false;
        //debug($id);
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $companycode = strtolower($this->Session->read('company_code'));
        $arr_data = $this->EmployeeExpenses->query("SELECT emp_expense.*,expense_item.category,emp_expense_details.*,emp_details.first_name,emp_details.last_name,expense_type.expense_type_pkey,expense_type.expense_type_name,site.site_name,site.site_id,site.site_pkey "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense_details.emp_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "where emp_expense_details.emp_expense_fkey = '$id' and emp_expense_details.status = 0");

        $total1 = $this->EmployeeExpenses->query("SELECT sum(total) from emp_expense_details where `emp_expense_fkey` = '$id' and status=0");
        $sum = round($total1['0']['0']['sum(total)'],2);
        $total2 = $this->EmployeeExpenses->query("SELECT sum(exp_amount) from emp_expense_details where `emp_expense_fkey` = '$id' and status=0");
        $sum_amount = round($total2['0']['0']['sum(exp_amount)'],2);
        $total3 = $this->EmployeeExpenses->query("SELECT sum(cgst) from emp_expense_details where `emp_expense_fkey` = '$id' and status=0");
        $sum_cgst = round($total3['0']['0']['sum(cgst)'],2);
        $total4 = $this->EmployeeExpenses->query("SELECT sum(sgst) from emp_expense_details where `emp_expense_fkey` = '$id' and status=0");
        $sum_sgst = round($total4['0']['0']['sum(sgst)'],2); 
        $total5 = $this->EmployeeExpenses->query("SELECT sum(igst) from emp_expense_details where `emp_expense_fkey` = '$id' and status=0");
        $sum_igst = round($total5['0']['0']['sum(igst)'],2); 
        $total5 = $this->EmployeeExpenses->query("SELECT sum(payment) from emp_expense_details where `emp_expense_fkey` = '$id' and status=0");
        $sum_payment = round($total5['0']['0']['sum(payment)'],2);
        $images = $this->EmployeeExpenses->query("SELECT image,expense_type.expense_type_name from emp_expense_details left join expense_type on (emp_expense_details.expense_type_fkey= expense_type.expense_type_pkey) where `emp_expense_fkey` = '$id' and emp_expense_details.status=0 and image != ''");
        $balance = $sum - $sum_payment;
        $data = ' <h3 style="text-align:center; ">Expense Details</h3> <hr style="border-top:1px solid #000; ">';
        $data .=' <table id="expensetable" class="table table-striped table-responsive" style="border-color:red; text-align: -webkit-auto; ">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th> Sl. No. </th>';
//        $data .=' <th> Emloyee </th>';
        $data .=' <th> Expense Name </th>';
        $data .=' <th> Category </th>';
        $data .=' <th> Related Party</th>';
        $data .=' <th> Amount </th>';
        $data .=' <th> CGST</th>';
        $data .=' <th> SGST</th>';
        $data .=' <th> IGST</th>';
        $data .=' <th> Total</th>';
        $data .=' <th> Payment</th>';
        $data .=' <th> Balance</th>';
        $data .=' <th> Remove</th>';
        //$data .= '</thead>';
        //$data .= '<tbody>';
        $i = 1;
        foreach ($arr_data as $value) {
            $class = ($i % 2) ? 'info' : 'danger';
            $pid = $value["emp_expense_details"]["expense_details_pkey"];
            $data .= '<tr>';
            $data .= '<td> ' . $i . '</td>';
//          $data .= '<td> ' . $value["emp_details"]["first_name"].' '.$value["emp_details"]["last_name"] . '</td>';
            $data .= '<td> ' . $value["expense_type"]["expense_type_name"] . '</td>';
            $data .= '<td> ' . $value["expense_item"]["category"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["related_party"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["exp_amount"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["cgst"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["sgst"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["igst"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["total"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["payment"] . '</td>';
            $data .= '<td> ' . $value["emp_expense_details"]["balance"] . '</td>';
//          if (isset($value["materialrequest"]["item_rate"]) && !empty($value["SalesOrderDetails"]["item_rate"])) {
          //$data .= '<td> <a href="#" class="  glyphicon glyphicon-pencil"onclick="editdaata(' . $rowindex . ',' . $pid . ');">&nbsp</a>'
            $data .=  '<td> <a href="#" class=" glyphicon glyphicon-remove btn-danger" onclick="removedaata(' . $rowindex . ',' . $pid . ');"></a></td>';
            $i++;
        }
        $data .= '</tr>';
        $data .= '<tr style="font-weight:bold;border-bottom: 1px solid #f4f4f4;"><td colspan="4" style="text-align:center;">Grand Total</td><td>'.$sum_amount.'</td><td>'.$sum_cgst.'</td><td>'.$sum_sgst.'</td><td>'.$sum_igst.'</td><td>'.$sum.'</td><td>'.$sum_payment.'</td><td>'.$balance.'</td><td></td></tr>';
        $data .= '</thead>';
        $data .= '</table>';
        $data .= '<table>';
        $data .= '<thead>';
        //$data .= '<tr>';
        $i = 1;
        if(!empty($images['0']["emp_expense_details"]["image"])){
        foreach($images as $val){
            if(!empty($val["emp_expense_details"]["image"])){
             $img = "https://mypayrollmaster.online/project/" . $companycode . "/".$val["emp_expense_details"]["image"];
             $name =  $val["expense_type"]["expense_type_name"];
            if($i % 5 == 1){
             $data .= '<tr>';
             $data .= '<td><img src ="'.$img.'" style="width:260px;height:350px;margin:5px;"><br><span style="text-align:center;">'.$name.'</span></td>';
            
            }else{
             $data .= '<td><img src ="'.$img.'" style="width:260px;height:350px;margin:5px;"><br><span style="text-align:center;">'.$name.'</span></td>';
            }
        if($i % 5 == 0){
         $data .= '</tr>';
        }
        $i++;
        }} 
        }else{}
        $data .= '</thead>';
        $data .= '</table>';
        echo $data;
    }
     public function deleteorder($id = 0,$total = 0) {
        $this->autoRender = false;
        $this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
            $this->ExpenseTypeDetails->updateAll(array('status' => 2), array('expense_details_pkey' => $id));
            //$this->MaterialRequestDetails->query("DELETE FROM emp_expense_details WHERE expense_details_pkey = $id");
            $key = $this->ExpenseTypeDetails->query("SELECT emp_expense_fkey from emp_expense_details where `expense_details_pkey` = '$id'");
            $fkey = $key['0']['emp_expense_details']['emp_expense_fkey'];
            $total = $this->ExpenseTypeDetails->query("SELECT sum(total) from emp_expense_details where `emp_expense_fkey` = '$fkey' and status=0");
            $sum = $total['0']['0']['sum(total)'];
            echo json_encode(array('msg' => "Expense successfully removed.",'total' => $sum));
        } else {
            echo json_encode(array('msg' => "Expense failed to removed",'total' => $sum));
        }
    }
    public function deleteordermaster($id = 0) {
        $this->autoRender = false;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
            $this->EmployeeExpenses->updateAll(array('status' => 2), array('emp_expenses_pkey' => $id));
            
            echo json_encode(array('msg' => 'Credit Note Request deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'Credit Note Request deleted.!'));
        }
    }
       public function category($type = '') {
        $this->autoRender = false;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
         if ($q != null) {
            $q_condition = "and category like '%$q%' ";
        } else {
            $q_condition = "";
        }
        $type_array = $this->EmployeeExpenses->query("select expense_item_pkey,category from expense_item where expense_item_name = '$type' and status = 1 $q_condition ORDER BY category asc ");
        
        $array = array();
        $types = array();
        $types[] = array("id" => "", "text" => "Select");
        foreach ($type_array as $key => $value) {
            $types[] = array(
                'id' => $value['expense_item']['expense_item_pkey'],
                'text' => $value['expense_item']['category'] 
            );
        }
        $array['items'] = $types;
        echo json_encode($array);
    }
    public function submit($total = 0,$balance = 0,$pid = 0,$payment_balance = '',$payment = 0){
        $this->autoRender = false;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
        if($total){
        $form_data['expenses_amount'] = $total;
        }
        if($payment){
        $form_data['payment'] = $payment;
        }
        if($balance){
        $form_data['balance'] = $balance;
        }
        $form_data['payment_status'] = "'".$payment_balance."'";
        $form_data['status'] = 1;
        $this->EmployeeExpenses->updateAll($form_data, array('emp_expenses_pkey' => $pid,'status'=>0));
        $this->ExpenseTypeDetails->updateAll(array('status' => 1), array('emp_expense_fkey' => $pid,'status'=>0));
        echo json_encode(array('msg' => 'Added Project Expense Successfully'));
    }
     public function view_expense($expenseId = 0) {

        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $exp_details = $this->EmployeeExpenses->query("SELECT emp_expense_details.*,emp_expense.*,expense_item.category,beneficiary.company_name,site.site_name,site.site_id,expense_type.expense_type_name,emp_details.first_name,emp_details.last_name "
                . "from emp_expense_details "
                . "left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where emp_expense_details.emp_expense_fkey = '$expenseId' and emp_expense_details.status = 1");
        $this->set('exp_details', $exp_details);
        
    }  
    public function show_expense($expenseId = 0) {

        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $exp_details = $this->EmployeeExpenses->query("SELECT emp_expense_details.*,emp_expense.*,expense_item.category,beneficiary.company_name,site.site_name,site.site_id,expense_type.expense_type_name,emp_details.first_name,emp_details.last_name "
                . "from emp_expense_details "
                . "left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where emp_expense_details.emp_expense_fkey = '$expenseId' and emp_expense_details.status = 1");
        $this->set('exp_details', $exp_details); 
        $exp_details1 = $this->EmployeeExpenses->query("SELECT sum(emp_expense_details.exp_amount) as a,sum(emp_expense_details.cgst)as b,sum(emp_expense_details.sgst)as c,sum(emp_expense_details.igst) as d,sum(emp_expense_details.total) as e "
                . "from emp_expense_details "
                . "where emp_expense_details.emp_expense_fkey = '$expenseId' and emp_expense_details.status = 1");
        $this->set("sum", $exp_details1);  
    }  
    public function downloads($pkey = 0) {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $this->ExpenseTypeDetails->useDbConfig = $this->Session->read('ds');
         $arr_data = $this->EmployeeExpenses->query("SELECT emp_expense.*,emp_expense_details.*,expense_type.expense_type_pkey,beneficiary.company_name, "
                . "expense_type.expense_type_name,site.site_name,site.site_id,site.site_pkey,emp_details.first_name,emp_details.last_name,expense_item.category "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "where emp_expense_details.emp_expense_fkey = '$pkey' and emp_expense.status = 1 and emp_expense_details.status = 1");
        
        $this->set("arr_data", $arr_data);

        $view = new View($this, false);
      
        $view_output = $view->render('download');
     
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
        try {
            $html2pdf = new HTML2PDF('L', 'A4', 'en');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->setTestTdInOnePage(false);
            $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
            //$html2pdf->writeHTML($content);
            $html2pdf->writeHTML($view_output);
            $html2pdf->Output('ProjectExpenseDetails.pdf', 'D');
            $this->render('download');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }
    
    
     public function downloadexcels($pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->EmployeeExpenses->query("SELECT emp_expense.*,emp_expense_details.*,expense_type.expense_type_pkey,beneficiary.company_name, "
                . "expense_type.expense_type_name,site.site_name,site.site_id,site.site_pkey,emp_details.first_name,emp_details.last_name,expense_item.category "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "where emp_expense_details.emp_expense_fkey = '$pkey' and emp_expense.status = 1 and emp_expense_details.status = 1");
        
        //$this->set("arr_data", $arr_data);
        $user_name = $this->Session->read('user_name');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $str_company_code = $this->Session->read('company_code');

        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_project_expense_details.xls" : "_project_expense_details" . strtotime() . ".xls";
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        //$arr_data['0']['site']['site_name'].'-'.$arr_data['0']['site']['site_id']
        $worksheet->setCellValueByColumnAndRow(0, 1, 'Project Expenses');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
        for ($col = 'A'; $col !== 'K'; $col++) {
            $objPHPExcel->getActiveSheet()
                    ->getColumnDimension($col)
                    ->setAutoSize(true);
        }
        $worksheet->mergeCells('A1:K1');
        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $col = 0;
        $worksheet->mergeCells('A2:K2');
        //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(15);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 2, 'Employee : ' . $arr_data['0']['emp_details']['first_name'].' '.$arr_data['0']['emp_details']['last_name']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $col = 0;
        $worksheet->mergeCells('A3:K3');
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 3, 'Beneficiary : ' . $arr_data['0']["beneficiary"]["company_name"]);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 3)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 3)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $worksheet->mergeCells('A4:K4');
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 4, 'Remarks : ' . $arr_data['0']['emp_expense']['remarks']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 4)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 4)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $worksheet->mergeCells('A5:K5');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 5)->getFont()->setSize(15);
        $worksheet->getStyle('A5')->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 5, $arr_data['0']['site']['site_name'].'-'.$arr_data['0']['site']['site_id']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 5)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 5)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $worksheet->mergeCells('A6:K6');
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 6, 'Request ID : ' . $arr_data['0']['emp_expense']['expense_id']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 6)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 6)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $worksheet->mergeCells('A7:K7');
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 7, 'Bil Date : ' .date('d-m-Y',strtotime($arr_data['0']['emp_expense']['expense_date'])));
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 7)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 7)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $worksheet->mergeCells('A8:K8');
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 8, 'GST Bill No. : ' .$arr_data['0']['emp_expense']['gst_bill_no']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 8)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 8)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $worksheet->mergeCells('A9:K9');
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 9, 'GST Bill Status : ' .$arr_data['0']['emp_expense']['gst_bill_status']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 9)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 9)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//        $worksheet->mergeCells('A10:J10');
//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 10, 'Total Expense : ' .$arr_data['0']['emp_expense']['expenses_amount']);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 10)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 10)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//        $worksheet->mergeCells('A11:J11');
//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 11, 'Payment : ' .$arr_data['0']['emp_expense']['payment']);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 11)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 11)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//        $worksheet->mergeCells('A12:J12');
//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 12, 'Balance : ' .$arr_data['0']['emp_expense']['balance']);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 12)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 12)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//        $worksheet->mergeCells('A13:J13');
//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 13, 'Payment Status : ' .$arr_data['0']['emp_expense']['payment_status']);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 13)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 13)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//        $worksheet->mergeCells('A14:J14');
        $rowcount = 10;
        $col = 0;
        $worksheet->mergeCells('A10:K10');
        $worksheet->getStyle('A10')->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 10)->getFont()->setSize(15);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 10, 'Expense Details ');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 10)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 10)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $rowcount = 11;
        $i = 0;

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl. No.');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Employee');
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Expense');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, ' Category');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
       
//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Date');
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Related Party');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Remarks added to excel by ARUL P DAS on 20/11/2019

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Amount');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'CGST');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Monthly Status added to excel by ARUL P DAS on 14/11/2019

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'SGST');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Remarks added to excel by ARUL P DAS on 20/11/2019
 
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 'IGST');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Remarks added to excel by ARUL P DAS on 20/11/2019
 
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, 'Total');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 'GST Bill No.');
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//
//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 'GST Bill Status');
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Monthly Status added to excel by ARUL P DAS on 14/11/2019
//
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 'Payment');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Remarks added to excel by ARUL P DAS on 20/11/2019

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 'Balance');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Monthly Status added to excel by ARUL P DAS on 14/11/2019

//        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, 'Payment Status');
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Remarks added to excel by ARUL P DAS on 20/11/2019

        
        $col = 5;
        $j = 0;
        $cgst_total = 0;
        $sgst_total = 0;
        $igst_total = 0;
        $amount = 0;
        $total = 0;
        $payment = 0;
        $balance = 0;
        $rowcount = $rowcount + 1;
        foreach ($arr_data as $val) {
                $j+=1;
                $col = 0;
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val['emp_details']['first_name'].'-'.$val['emp_details']['last_name']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val['expense_type']['expense_type_name']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $val['expense_item']['category']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $val['emp_expense_details']['exp_date']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $val['emp_expense_details']['related_party']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $val['emp_expense_details']['exp_amount']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $val['emp_expense_details']['cgst']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $val['emp_expense_details']['sgst']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $val['emp_expense_details']['igst']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $val['emp_expense_details']['total']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $val['emp_expense_details']['gst_bill_no']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $val['emp_expense_details']['gst_bill_status']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $val['emp_expense_details']['payment']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $val['emp_expense_details']['balance']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $val['emp_expense_details']['payment_status']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $rowcount++;
                $cgst_total = $cgst_total + $val['emp_expense_details']['cgst'];
                $sgst_total = $sgst_total + $val['emp_expense_details']['sgst'];
                $igst_total = $igst_total + $val['emp_expense_details']['igst'];
                $amount = $amount + $val['emp_expense_details']['exp_amount'];
                $total = $total + $val['emp_expense_details']['total'];
                $payment = $payment + $val['emp_expense_details']['payment'];
                $balance = $balance+ $val['emp_expense_details']['balance'];
        }
       // $rowcount = $rowcount + 1;
        $col = 0;
        $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Grand Total');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +4) . $rowcount, $amount);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +4, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +5) . $rowcount, $cgst_total);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +5, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +6) . $rowcount, $sgst_total);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +6, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +6, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +7) . $rowcount, $igst_total);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +7, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +7, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +8) . $rowcount, $total);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +8, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +8, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +9) . $rowcount, $payment);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +9, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +9, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +10) . $rowcount, $balance);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $rowcount = $rowcount + 1;
        $col = 0;
        $worksheet->mergeCells('A'.$rowcount.':J'.$rowcount);
         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
        );
         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Total');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +10) . $rowcount, $total);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $rowcount = $rowcount + 1;
        $col = 0;
        $worksheet->mergeCells('A'.$rowcount.':J'.$rowcount);
         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
        );
        //$payment = isset($arr_data['0']['emp_expense']['payment'])?$arr_data['0']['emp_expense']['payment']:'0'; 
        $bal = $total - $payment;
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Amount Paid');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +10) . $rowcount, $payment);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $rowcount = $rowcount + 1;
        $col = 0;
        $worksheet->mergeCells('A'.$rowcount.':J'.$rowcount);
         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
        );
         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Balance');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +10) . $rowcount, $bal);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        
        $rowcount = $rowcount + 1;
        $col = 0;
        $worksheet->mergeCells('A'.$rowcount.':J'.$rowcount);
         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
        );
        $status = $arr_data['0']['emp_expense']['payment_status'];
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Payment Status');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col +10) . $rowcount, $status);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col +10, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        
        $objPHPExcel->getActiveSheet()->setTitle('Project Expense Details');
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    public function loadnewpurchase() {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        //$id_array = $this->EmployeeExpenses->query("SELECT `expense_id`,emp_expenses_pkey FROM `emp_expense` WHERE `status` = '1'");
        //$this->set("expense_list", $id_array);
        $id_array = $this->EmployeeExpenses->query("SELECT distinct site_pkey,site_name,site_id from site left join emp_expense on (emp_expense.vendor = site.site_pkey) WHERE site.status = 1 and emp_expense.status = 1");
        $this->set("site_list", $id_array);
        $this->render('loadnewpurchase');
    }
    
//    public function project($type = '') { 
//        $this->autoRender = false;
//        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
//        
//        $type_array = $this->EmployeeExpenses->query("select site_name,site_id,site_pkey  from site left join emp_expense on (emp_expense.vendor = site.site_pkey) where emp_expense.emp_expenses_pkey = '$type' and site.status = 1 ");
//        
//        $array = array();
//        $types = array();
//        $types[] = array("id" => "", "text" => "Select");
//        foreach ($type_array as $key => $value) {
//            $types[] = array(
//                'id' => $value['site']['site_pkey'],
//                'text' => $value['site']['site_name'] 
//            );
//        }
//        $array['items'] = $types;
//        echo json_encode($array);
//    }
    
    public function beneficiary($type = '') { 
        $this->autoRender = false;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        
        $type_array = $this->EmployeeExpenses->query("select distinct contact_id,company_name from beneficiary left join emp_expense on (emp_expense.beneficiary_fkey = beneficiary.contact_id) where emp_expense.vendor = '$type' and beneficiary.status = 1 ");
        
        $array = array();
        $types = array();
        $types[] = array("id" => "", "text" => "Select");
        foreach ($type_array as $key => $value) {
            $types[] = array(
                'id' => $value['beneficiary']['contact_id'],
                'text' => $value['beneficiary']['company_name'] 
            );
        }
        $array['items'] = $types;
        echo json_encode($array);
    }
    public function expense_type($type = '') { 
        $this->autoRender = false;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $type_array = $this->EmployeeExpenses->query("select distinct expense_type_pkey,expense_type_name from emp_expense left join emp_expense_details on (emp_expense.emp_expenses_pkey = emp_expense_details.emp_expense_fkey) left join expense_type on(expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) where emp_expense.vendor = '$type' and emp_expense.beneficiary_fkey is NULL and expense_type.status = 1 and emp_expense_details.status = 1 and return_status = 0");
        $array = array();
        $types = array();
        $types[] = array("id" => "", "text" => "Select");
        foreach ($type_array as $key => $value) {
            $types[] = array(
                'id' => $value['expense_type']['expense_type_pkey'],
                'text' => $value['expense_type']['expense_type_name'] 
            );
        }
        $array['items'] = $types;
        echo json_encode($array);
    }
     public function expense_typelist($type = '',$ben = '') { 
        $this->autoRender = false;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $type_array = $this->EmployeeExpenses->query("select expense_type_pkey,expense_type_name from emp_expense left join emp_expense_details on (emp_expense.emp_expenses_pkey = emp_expense_details.emp_expense_fkey) left join expense_type on(expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) where emp_expense.vendor = '$type' and emp_expense.beneficiary_fkey = '$ben' and expense_type.status = 1 and emp_expense_details.status = 1 and return_status = 0");
        $array = array();
        $types = array();
        $types[] = array("id" => "", "text" => "Select");
        foreach ($type_array as $key => $value) {
            $types[] = array(
                'id' => $value['expense_type']['expense_type_pkey'],
                'text' => $value['expense_type']['expense_type_name'] 
            );
        }
        $array['items'] = $types;
        echo json_encode($array);
    }
     public function request_id($vendor = '',$type = '',$ben = '') { 
        $this->autoRender = false;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        if($ben == ''){
            $cond = " and emp_expense.beneficiary_fkey is NULL ";
        }else{
            $cond = " and emp_expense.beneficiary_fkey = '$ben' ";
        }
        $type_array = $this->EmployeeExpenses->query("select emp_expenses_pkey,expense_id from emp_expense left join emp_expense_details on (emp_expense.emp_expenses_pkey = emp_expense_details.emp_expense_fkey) left join expense_type on(expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) where emp_expense.vendor = '$vendor' $cond and emp_expense_details.expense_type_fkey = '$type' and expense_type.status = 1 and emp_expense_details.status = 1 and return_status = 0");
        $array = array();
        $types = array();
        $types[] = array("id" => "", "text" => "Select");
        foreach ($type_array as $key => $value) {
            $types[] = array(
                'id' => $value['emp_expense']['emp_expenses_pkey'],
                'text' => $value['emp_expense']['expense_id'] 
            );
        }
        $array['items'] = $types;
        echo json_encode($array);
    }
    public function get_details($id,$type) {
        $this->autoRender = false;
        $arr_request_data = $this->request->data;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $expense_type_array = $this->EmployeeExpenses->query("SELECT `expense_type_pkey`, `expense_type_code`, `expense_type_name` FROM `expense_type` WHERE status=1 order by expense_type_name asc");
        //$this->set("expense_type", $expense_type_array);
        //$array['expense_type'] = $expense_type_array;
        $emp_list = $this->EmployeeExpenses->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 ORDER BY first_name ASC');
        //$this->set("arr_employees", $emp_list);
        //$array['arr_employees'] = $emp_list;
        $exp_details = $this->EmployeeExpenses->query("SELECT emp_expense_details.*,expense_item.category,expense_type.expense_type_name "
                . "from emp_expense_details "
                . "left join emp_expense on (emp_expense.emp_expenses_pkey = emp_expense_details.emp_expense_fkey) "
                . "left join expense_item on (expense_item.expense_item_pkey = emp_expense_details.category_fkey) "
                . "left join expense_type on (expense_type.expense_type_pkey = emp_expense_details.expense_type_fkey) "
                . "where emp_expense_details.emp_expense_fkey = '$id' and emp_expense_details.expense_type_fkey = '$type' and emp_expense_details.status = 1");
       
        //$this->set("data", $exp_details);
        $exp_fkey = $exp_details['0']['emp_expense_details']['expense_details_pkey'];
        $results= $this->EmployeeExpenses->query("SELECT expense_date from emp_expense_payment where `expense_details_fkey` = '$exp_fkey' and status=1 order by payment_pkey desc limit 1 ");
        $date = $results['0']['emp_expense_payment']['expense_date'];
        //$this->set("date",$date);
        //$array['date'] = $date;
        $exp_details1 = $this->EmployeeExpenses->query("SELECT sum(emp_expense_details.exp_amount)as a,sum(emp_expense_details.cgst)as b,sum(emp_expense_details.sgst)as c,sum(emp_expense_details.igst)as d,sum(emp_expense_details.total) as e  "
                . "from emp_expense_details "
                . "where emp_expense_details.emp_expense_fkey = '$id' and emp_expense_details.expense_type_fkey = '$type' and emp_expense_details.status = 1");
        $array['payment'] = round($exp_details['0']['emp_expense_details']['payment'],2);
        $array['payment_status'] = $exp_details['0']['emp_expense_details']['payment_status'];
        $array['emp_expense_fkey'] = $exp_details['0']['emp_expense_details']['emp_expense_fkey'];
        $array['expense_details_pkey'] = $exp_details['0']['emp_expense_details']['expense_details_pkey'];
        $array['paid_total'] = round($exp_details['0']['emp_expense_details']['total'],2);
        $array['a'] = round($exp_details1['0']['0']['a'],2);
        $array['b'] = round($exp_details1['0']['0']['b'],2);
        $array['c'] = round($exp_details1['0']['0']['c'],2);
        $array['d'] = round($exp_details1['0']['0']['d'],2);
        $array['e'] = round($exp_details1['0']['0']['e'],2);
        //$array['exp_details'] = $exp_details;
        echo json_encode($array);
    }
}
                