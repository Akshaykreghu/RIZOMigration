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
class SalaryComponentUploadController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'SalaryComponentUpload';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'EmployeeConfig', 'Family', 'passport', 'Promotion', 'NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmployeeSalaryStructure', 'SalaryStructures', 'EmpSalaryCompUpload', 'SalaryHeadItems', 'ComponentIncrement');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index()
    {
        $arr_request_data = $this->request->data;
        // debug($arr_request_data);
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $arr_leavetypes = $this->SalaryHeadItems->query("select salaLeaveRequestsry_head_item_pkey,item from salary_head_items where head_fkey=6 and upper(value)='Y'");
        $arr_structure = $this->SalaryStructures->find("all", array("conditions" => array("structure_active" => 1)));
        $this->set("arr_structure", $arr_structure);
        //edited by sinsiya 26-02-2024 
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $conditions = array("status" => 1);
            $conditions = array("branch_code !=" => $branch);
            //$arr_branches = $this->Units->find("all", array("conditions" => $conditions));
            $arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions));
            // debug($arr_employees);
            // exit;
        } else {

            $arr_employees = $this->EmployeeDetails->find("all");
        }

        // Edited by Akshay on 10-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $conditions = array('status' => 1);
        $is_ho = 1;
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions = array('status' => 1, 'branch_code' => $is_ho);
            }
        }
        $this->set("is_ho", $is_ho);
        $arr_branches = $this->Units->find("all", array("conditions" => $conditions));
        // End
        $this->set("arr_branches", $arr_branches);

        //debug($arr_employees);
        $this->set("arr_employees", $arr_employees);
    }
    public function showsalaryupload() {}
    public function branchwiss($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($branch != null) {
            $branch_condition = "and branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select * from emp_details where status = 1 $branch_condition");
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function saveuploads()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        //$branch = $arr_form_data['branch_code'];
        $emp_key = $arr_form_data['filterby_employee'];
        // $strct = $arr_form_data['structure_key'];
        // $comp = $arr_form_data['Components'];
        // $amount = $arr_form_data['comp_amount'];
        $data = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_key");

        // debug($item);
        $emp_id = $data['0']['emp_details']['emp_id'];

        // Edited by Akshay on 4-12-2025
        $this->EmployeeDetails->query("UPDATE emp_salcomp_upload SET status = 0  WHERE status = 1;");
        // End

        foreach ($arr_form_data['salary_head_item_fkey'] as $key => $value) {
            //debug($value);
            $item = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $key");
            $s_item = $item['0']['salary_head_items']['item'];

            $out['emp_id'] = $emp_id;
            $out['salary_head_item_fkey'] = $key;
            if ($value == '') {
                $out['rate'] = 0;
            } else {
                $out['rate'] = $value;
            }
            $out['component'] = $s_item;
            $out['created_by'] = $this->Session->read('login_user_id');
            $result = $this->EmpSalaryCompUpload->saveAll($out);
        }

        // $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('', @pmessage);");

        //****structure changes by megha adding distribution of salary on 20/04/2022****
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $arr_usercredentials = $this->EmployeeDetails->find('first', array(
            'fields' => 'emp_pkey',
            'conditions' => array(
                'emp_id' => $emp_id
            )
        ));
        $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';
        $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('$emp', @pmessage);");

        $salary = $this->EmployeeSalaryStructure->find(
            "all",
            array(
                'fields' => 'emp_structure_id',
                'conditions' => array(
                    'emp_fkey' => $emp,
                    'end_date_effective is null'
                )
            )
        );

        $company = $this->Session->read('company_code');
        $user_ids = $this->Session->read('login_user_id');

        $salary_id = isset($salary['0']['EmployeeSalaryStructure']['emp_structure_id']) ? $salary['0']['EmployeeSalaryStructure']['emp_structure_id'] : "''";
        $proc = $this->EmployeeSalaryStructure->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
        $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
            "all",
            array(
                'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                'conditions' => array(
                    'emp_structure_id' => $salary_id,
                    'emp_fkey' => $emp,
                    'remarks IS NOT NULL',
                    'end_date_effective is null'
                )
            )
        );
        foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
            $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
            $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
            $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';
            $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';

            if (!empty($formula_from_remarks)) {
                //edited by sinsiya on 20-08-2025
                $components = $this->EmployeeSalaryStructure->find('list', array(
                    'fields' => array('salary_head_item_desc', 'structure_det_value'),
                    'conditions' => array(
                        'emp_fkey' => $emp,
                        'end_date_effective is null'
                    )
                ));

                // Replace placeholders like ComponentName(...) with values
                $formula_from_remarks = preg_replace_callback(
                    '/([A-Za-z_]+)(?:\((.*?)\))?/',
                    function ($matches) use ($components) {
                        $componentName = $matches[1]; // e.g. DearnessAllowance or SpecialAllowance
                        if (isset($components[$componentName])) {
                            return $components[$componentName]; // replace with actual value
                        }
                        return 0; // default if not found
                    },
                    $formula_from_remarks
                );
                //end by sinsiya
                $salary_amount = '0';
                eval('$salary_amount = ' . $formula_from_remarks . ';');
                if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employer Contribution' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employee Contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                    if ($head_operator == 'Deduction') {
                        $salary_amount = ceil($salary_amount);
                    } else {
                        $salary_amount = round($salary_amount);
                    }
                } else {
                    $salary_amount = round($salary_amount);
                }
                if ($head_operator == 'Deduction') {
                    $salary_amount *= -1;
                }
                $arr_emp_salary_slip_data = array(
                    'EmployeeSalaryStructure.structure_det_value' => round($salary_amount)
                );
                $this->EmployeeSalaryStructure->updateAll(
                    $arr_emp_salary_slip_data,
                    array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                );
            }
        }
        $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
        $resp = array();

        $resp["success"] = 1;
        $resp["msg"] = "Salary component saved successfully.";
        echo json_encode($resp);
        // }else{

        //     $resp = array();
        //     $resp["success"] = 0;


        //     $resp["msg"] = "EMI amount should not be greater than balance amount";
        //     echo json_encode($resp);
        // }
    }

    public function saveuploads_od()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $branch = $arr_form_data['branch_code'];
        $emp_key = $arr_form_data['emp_pkey'];
        $strct = $arr_form_data['structure_key'];
        $comp = $arr_form_data['Components'];
        $amount = $arr_form_data['comp_amount'];
        $data = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_key");
        $item = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $comp");
        // debug($item);
        $emp_id = $data['0']['emp_details']['emp_id'];
        $s_item = $item['0']['salary_head_items']['item'];
        // debug($s_item);
        $out['emp_id'] = $emp_id;
        $out['salary_head_item_fkey'] = $comp;
        $out['rate'] = $amount;
        $out['component'] = $s_item;
        $out['created_by'] = $this->Session->read('login_user_id');






        $result = $this->EmpSalaryCompUpload->saveAll($out);
        $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('', @pmessage);");
        $resp = array();

        $resp["success"] = 1;
        $resp["msg"] = "Employee loan Save successfully";
        echo json_encode($resp);
        // }else{

        //     $resp = array();
        //     $resp["success"] = 0;


        //     $resp["msg"] = "EMI amount should not be greater than balance amount";
        //     echo json_encode($resp);
        // }
    }

    public function component_upload()
    {

        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        // debug($_REQUEST['emp_ctc_upload_pkey']);die();
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructures->useDbconfig = $this->Session->read('ds');
        // $this->set("arr_employee", $arr_employee = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        //edited by sinsiya 26-02-2024 
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            //$conditions = array("status" => 1);
            $conditions = "ed.branch_code != '" . $branch . "'";
            $conditions1 = "EmployeeDetails.branch_code != '" . $branch . "'";
            $arr_branches = $this->EmployeeDetails->query("select ed.branch_code,branch.branch_name from emp_details ed left join branches as branch on (ed.branch_code = branch.branch_code) where branch.status = 1 and " . $conditions . " group by ed.branch_code ");
            $arr_employees = $this->EmployeeDetails->query("select EmployeeDetails.emp_pkey,CONCAT(first_name,' ', ifnull(last_name,'')) as name,"
                . "emp_proff.emp_company_id from emp_details as EmployeeDetails inner join emp_proff on (emp_proff.emp_fkey = EmployeeDetails.emp_pkey) "
                . "where EmployeeDetails.status= '1' and " . $conditions1 . " order by first_name ASC");
            //debug($arr_branches);
            // $arr_branches = $this->Units->find("all", array("conditions" => $conditions));
            //$arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions));
        } else {
            $arr_branches = $this->EmployeeDetails->query("select ed.branch_code,branch.branch_name from emp_details ed left join branches as branch on (ed.branch_code = branch.branch_code) where branch.status = 1 group by ed.branch_code ");
            $arr_employees = $this->EmployeeDetails->query("select EmployeeDetails.emp_pkey,CONCAT(first_name,' ', ifnull(last_name,'')) as name,"
                . "emp_proff.emp_company_id from emp_details as EmployeeDetails inner join emp_proff on (emp_proff.emp_fkey = EmployeeDetails.emp_pkey) "
                . "where EmployeeDetails.status= '1' order by first_name ASC");
        }

        $this->set('arr_branches', $arr_branches);

        $arr_structure = $this->EmployeeDetails->query("select structure_id,structure_name from salary_structure where structure_active= 1");
        $this->set('arr_structure', $arr_structure);
        // debug($arr_structure);
        // $this->set("arr_structure", $arr_structure = $this->EmployeeSalaryStructure->find("all", array('conditions' => array('item_part' => 'Direct'))));
        // debug($arr_structure);
        // $this->set("arr_components", $arr_components = $this->EmployeeSalaryStructure->find("all", array('conditions' => array('head_operator' => 'Addition'))));
        // debug($arr_components);
        $data['emp_salcomp_upload_pkey'] = 0;
        $data['transaction_id'] = '';
        $data['emp_id'] = '';
        $data['salary_head_item_fkey'] = '';
        $data['component'] = '';
        $data['rate'] = '';
        // $data['emp_tds_deducted'] = '';
        if (isset($_REQUEST['emp_salarycomp_upload_pkey']) && $_REQUEST['emp_salarycomp_upload_pkey'] != 0) {
            $data_db = $this->EmployeeCTC->find("first", array("conditions" => array("emp_salarycomp_upload_pkey" => $_REQUEST['emp_salarycomp_upload_pkey'])));
            $data = $data_db['EmployeeCTC'];
            // debug($data);
        }
        // debug($data);


        $this->set("arr_employees", $arr_employees);

        $this->set("data", $data);
        //$this->layout = null;
    }
    public function getComp()
    {
        $this->autoRender = false;
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $emp_key = $arr_form_data['employee'];
        $branch = $arr_form_data['branch'];
        $structure = $arr_form_data['struct'];


        $arr_comp = $this->EmployeeSalaryStructure->query("select   emp_salary_structure_pkey,emp_fkey,emp_structure_id,salary_head_item_fkey,salary_head_item_desc,structure_det_value,head_operator,item_part
from emp_salary_structure where emp_fkey ='$emp_key' and emp_structure_id = '$structure' and head_operator = 'Addition' group by salary_head_item_fkey ");
        // debug($arr_comp);

        $appnds = '';

        // $appnds.='<option>select</option>';
        foreach ($arr_comp as $val) {
            // debug($val);
            $appnds .= '<option value="' . $val['emp_salary_structure']['salary_head_item_fkey'] . '" >' . $val['emp_salary_structure']['salary_head_item_desc'] . '</option>';
        }
        //        // debug($arr_loan_data);
        echo json_encode(array("success" => 1, "data" => $appnds));
    }

    public function load()
    {
        $this->autoRender = FALSE;
        $this->layout = null;

        $data['empname'] = "";
        $data['salary_head_item_desc'] = "";
        $data['structure_det_value'] = "";

        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['emp_pkey']) && $_REQUEST['emp_pkey'] != 0) {
            $data_db = $this->EmployeeSalaryStructure->find("first", array("conditions" => array("emp_pkey" => $_REQUEST['emp_pkey'])));
            $data = $data_db['EmployeeSalaryStructure'];
        }
        $respdata = array('success' => true, "data" => $data);
        echo json_encode($respdata);
    }


    public function listemployee()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        // debug($arr_request_data);
        // $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        // $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        // debug($emp_fkey);
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbconfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;

        // debug($page);
        // debug($limit);
        // debug($ofst);
        // debug($ofst);
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        // $branch_condition = '';
        // $emp_condition = '';

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp_fkey = $arr_request_data['employee'];
            $emp_cond = "and ed.emp_pkey= $emp_fkey ";
        } else {
            $emp_fkey = 'emp_fkey';
            $emp_cond = " ";
        }

        // debug($emp_fkey);
        // debug($arr_request_data['employee']);
        //  debug($arr_request_data['branch']);
        $bb = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        // debug($bb);

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read('emp_fkey');
            $payroUser = $this->EmployeeCTC->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];

            $branch_code = " ed.branch_code!='$branch' and ed.branch_code='$bb' ";
        } else {
            if ($bb != '') {

                $branch_code = " ed.branch_code='$bb' ";
            }
            if ($bb == '') {
                $branch_code = " ed.branch_code = ed.branch_code";
            }
            if ($bb == '0') {
                $branch_code = " ed.branch_code = ed.branch_code";
            }
        }


        $emp_data = isset($arr_request_data['emp']) ? $arr_request_data['emp'] : '';
        // debug($emp_data);
        if ($emp_data != '') {

            $emp_condition = " and ed.emp_pkey='$emp_data' ";
        }
        if ($emp_data == '') {
            $emp_condition = " and ed.emp_pkey= ed.emp_pkey";
        }
        if ($emp_data == 0) {
            $emp_condition = " and ed.emp_pkey = ed.emp_pkey";
        }
        $structure = isset($arr_request_data['structure']) ? $arr_request_data['structure'] : '';
        // debug($structure);
        if ($structure != '') {

            $structure_condition = " and ss.structure_id='$structure' ";
        }
        if ($structure == '') {
            $structure_condition = " and ss.structure_id= ss.structure_id";
        }
        if ($structure == 0) {
            $structure_condition = " and ss.structure_id= ss.structure_id";
        }
        // debug($emp_condition);



        $count = $this->EmployeeCTC->query(
            "select count(esc.emp_salcomp_upload_pkey) from emp_salcomp_upload esc left join emp_details as ed on (esc.emp_id = ed.emp_id)
left join emp_salary_structure as ectc on (ed.emp_pkey = ectc.emp_fkey)
left join salary_structure as ss on (ectc.emp_structure_id = ss.structure_id)
 where $branch_code $emp_condition  $structure_condition  and ectc.head_operator='Addition' and esc.salary_head_item_fkey != '1000' and ectc.item_part='Direct' and end_date_effective is null group by esc.emp_salcomp_upload_pkey  ORDER BY emp_salary_structure_pkey desc"
            // . " limit $limit "
            // . " offset $ofst "
        );

        $data = count($count);
        // debug($data);
        // debug($count);
        // debug($counts);
        $count = $data;
        // debug($count);
        $arr_att = $this->EmployeeCTC->query(
            "select concat(ed.first_name,' ',ed.last_name) as name,ed.emp_pkey,ed.emp_id,esc.*from emp_salcomp_upload esc left join emp_details as ed on (esc.emp_id = ed.emp_id)
left join emp_salary_structure as ectc on (ed.emp_pkey = ectc.emp_fkey)
left join salary_structure as ss on (ectc.emp_structure_id = ss.structure_id)
 where $branch_code $emp_condition  $structure_condition  and ectc.head_operator='Addition' and esc.salary_head_item_fkey != '1000' and ectc.item_part='Direct' and end_date_effective is null group by esc.emp_salcomp_upload_pkey  ORDER BY esc.emp_salcomp_upload_pkey desc"
                . " limit $limit "
                . " offset $ofst "

        );
        // debug($arr_att);



        $out = array();
        //debug($arr_att);
        foreach ($arr_att as $key => $value) {
            // $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : ''
            $out['emp_id'] = isset($value['ed']['emp_id']) ? $value['ed']['emp_id'] : '';
            $out['emp_name'] = isset($value['0']['name']) ? $value['0']['name'] : '';
            $out['salary_head_item_desc'] = isset($value['esc']['component']) ? $value['esc']['component'] : '';
            $out['structure_det_value'] = isset($value['esc']['rate']) ? $value['esc']['rate'] : '';
            // $out['']

            //            $out['end_date_effective'] = isset($value['au']['end_date_effective']) ? $value['au']['end_date_effective'] : '';
            $resp_att["rows"][$key] = $out;
            $resp_att["total"] = $count;
        }

        // debug($resp_att);
        echo json_encode($resp_att);
    }
    public function filterjson($branch = '', $structure = '')
    {
        $this->autoRender = false;
        // $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        // debug($branch);
        // debug($structure);
        // debug($q);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        // debug($q);
        if ($branch != '0') {
            $branch_condition = "and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = " and emp_details.branch_code = emp_details.branch_code";
        }
        if ($q != null) {
            $q_condition = "and first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ";
        } else {
            $q_condition = " ";
        }
        if ($structure != 0) {
            $Str_cond = " and emp_proff.structure_id in ('$structure') ";
        } else {
            $Str_cond = " and emp_proff.structure_id = emp_proff.structure_id ";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) left join emp_salary_structure as ectc on (emp_details.emp_pkey = ectc.emp_fkey) where emp_details.status = 1 $branch_condition $Str_cond  $q_condition group by emp_details.emp_id ORDER BY emp_pkey DESC ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    public function jsonbranch($branch = 0)
    {
        $this->autoRender = false;
        // $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        // debug($branch);
        // debug($structure);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = "and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        if ($q != null) {
            $q_condition = "and first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 $branch_condition $q_condition ORDER BY emp_pkey DESC ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function downloadempctcformat($employee = '', $branch = '', $structure = '')
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbconfig = $this->Session->read('ds');
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_component_upload.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet(1);
        $dqlToFetchComponentHeads = "select DISTINCT(salary_head_item_desc) as salary_head_item_desc, salary_head_item_fkey from emp_details ed "

            . "join emp_salary_structure as ectc on(ed.emp_pkey = ectc.emp_fkey) "
            . "join salary_head_items  on(salary_head_items.salary_head_item_pkey = ectc.salary_head_item_fkey) "
            . "where ectc.head_operator='Addition' and ectc.item_part='Direct' and salary_head_items.head_fkey = 1 and  (ectc.end_date_effective='0000-00-00' or ectc.end_date_effective is null) order by salary_head_item_order1 ASC";

        //        $dqlToFetchComponentHeads = "select DISTINCT(salary_head_item_desc) as salary_head_item_desc from emp_details ed "
        //                . "join emp_salary_structure as ectc on(ed.emp_pkey = ectc.emp_fkey) "
        //                . "where ectc.head_operator='Addition' and ectc.item_part='Direct' and end_date_effective is null  ORDER BY ed.emp_pkey,ed.first_name ASC; ";
        $arr_salary_components = $this->EmployeeCTC->query($dqlToFetchComponentHeads);




        $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
        $worksheet->setCellValueByColumnAndRow(1, 1, "Company ID");
        $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Name");
        $worksheet->setCellValueByColumnAndRow(3, 1, "Monthly Gross Salary"); //Edited by Akshay on 14-10-2024
        // $worksheet->setCellValueByColumnAndRow(3, 1, "Amount");
        foreach ($arr_salary_components as $key => $value) {
            # code...
            // debug($value);
            $worksheet->setCellValueByColumnAndRow($key + 4, 1, trim($value['ectc']['salary_head_item_desc'])); //Edited by Akshay on 14-10-2024
        }

        // die();


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        // $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        // $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);

        for ($col = 'D'; $col !== 'X'; $col++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth(20);
            $objPHPExcel->getActiveSheet()->getStyle($col . '1')->getFont()->setBold(true);
        }

        // $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setVisible(false);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);

        $headerRow = array("Employee Id", "Company Id", "Employee Name", "Monthly Gross Salary", "components", "Amount"); //Edited by Akshay on 14-10-2024


        if ($branch != '0') {
            $conditions = " and ed.branch_code = '$branch'";
        } else {
            $conditions = " and ed.branch_code = ed.branch_code";
        }

        if ($employee != "null" && $employee != 0) {
            $conditions .= " and ed.emp_pkey ='$employee'";
        } else {
            $conditions .= " and ed.emp_pkey = ed.emp_pkey";
        }

        // Edited by Akshay on 14-3-2025
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'GAAR' || $user == 'HRBL')) {
            $user_id = $this->Session->read("login_user_id"); //user id
            $special_access = $this->EmployeeCTC->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
            $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;

            if ($special_access != 1) {
                $directors_branch = $this->EmployeeCTC->query("SELECT get_directors_branch_code() AS branch;");
                $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                $conditions .= " and ed.branch_code != '$directors_branch' ";
            }
        }
        // End

        // if ($structure != 0){

        //     $conditions .= " and ss.structure_id = '$structure'";
        // }
        // if($structure == 0){
        //     $conditions .= " and ss.structure_id = ss.structure_id ";
        // }
        $arr_att = $this->EmployeeCTC->query("select CONCAT(ed.first_name,' ',ed.last_name) AS emp_name,ed.emp_pkey,ed.emp_id,emp_proff.emp_company_id from emp_details ed left join emp_proff on (ed.emp_pkey = emp_proff.emp_fkey)  WHERE status = 1 $conditions ORDER BY ed.emp_pkey,ed.first_name ASC");



        $this->set("arr_att", $arr_att);
        $data = array();
        $counts = count($arr_att);
        $resp_att = array();
        $resp_att["rows"] = array();

        if (count($arr_att) > 0) {

            $rowindex = 2;

            foreach ($arr_att as $key => $value) {
                $emp_id = isset($value['ed']['emp_id']) ? $value['ed']['emp_id'] : '';
                $emp_name = isset($value['0']['emp_name']) ? $value['0']['emp_name'] : '';
                $comp_name = isset($value['emp_proff']['emp_company_id']) ? $value['emp_proff']['emp_company_id'] : '';
                //Edited by Akshay on 14-10-2024
                $emp = isset($value['ed']['emp_pkey']) ? $value['ed']['emp_pkey'] : '';
                $arr_gross_sal = $this->EmployeeCTC->query("SELECT SUM(round(structure_det_value)) AS gross_sal FROM emp_salary_structure ectc 
                                                                LEFT join salary_head_items  on(salary_head_items.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                                                WHERE ectc.emp_fkey = '$emp' AND ectc.head_operator='Addition' and ectc.item_part='Direct' 
                                                                and salary_head_items.head_fkey = 1 
                                                                and  (ectc.end_date_effective='0000-00-00' or ectc.end_date_effective is null) ");
                $monthly_gross_sal = isset($arr_gross_sal[0][0]['gross_sal']) ? $arr_gross_sal[0][0]['gross_sal'] : 0;
                //End
                // $salary_head_item_desc = isset($value['ectc']['salary_head_item_desc']) ? $value['ectc']['salary_head_item_desc'] : '';
                // $structure_det_value = isset($value['ectc']['structure_det_value']) ? $value['ectc']['structure_det_value'] : '';
                // $salary_head_item_fkey =isset($value['ectc']['salary_head_item_fkey']) ? $value['ectc']['salary_head_item_fkey'] : '';
                // foreach ($value as $columns) {
                // foreach ($columns as $column) {
                $columnindex = 0;
                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $emp_id);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $comp_name);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $emp_name);
                //Edited by Akshay on 14-10-2024
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $monthly_gross_sal);
                //End

                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex , 100);
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex , 100);
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex , 100);
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex , 100);
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $salary_head_item_desc);
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $structure_det_value);
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $salary_head_item_fkey);

                $rowindex++;
            }

            if ($columnindex <= 4) {
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('Salary Components Upload ');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempctc($ctcuploadtype = 0)
    {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);


        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            $arr_rejected_emps = array(); // Edited by Akshay on 17-1-2025
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                $unbalace_sal = false; //Edited by Akshay on 4-10-2024

                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;


                                // debug($arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()]);
                            }
                            $index++;
                        }
                    }

                    if ($mandatory_fields_warning) {

                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {

                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

                        // Edited by Akshay on 4-12-2025
                        $this->EmployeeDetails->query("UPDATE emp_salcomp_upload SET status = 0 WHERE status = 1;");
                        // End 

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        // debug($arrayempdata);

                        foreach ($arrayempdata as $key => $row) {

                            $arr_empctc_data = array();

                            $emp_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            // $com = isset($row['components']) ? $row['components'] : '';
                            // $Amount = isset($row['Amount']) ? $row['Amount'] : '0';
                            // $head_fkey = isset($row['Salary Head fkey']) ? $row['Salary Head fkey'] : '';  

                            // if($emp_id == ''){
                            //     continue;
                            // }

                            $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                                'fields' => 'emp_pkey',
                                'conditions' => array(
                                    'emp_id' => $emp_id
                                )
                            ));
                            $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';
                            // debug($emp);

                            //Edited by Akshay on 14-10-2024
                            $emp_company_id = isset($row['Company ID']) ? $row['Company ID'] : '';
                            $emp_name = isset($row['Employee Name']) ? $row['Employee Name'] : '';
                            $total_sal = 0;
                            $monthly_gross_sal = 0;

                            foreach ($row as $key => $value) {
                                if ($key == 'Monthly Gross Salary') {
                                    $monthly_gross_sal = $value;
                                }
                                if (!in_array($key, array("Employee ID", "Company ID", "Employee Name", "Monthly Gross Salary"))) {
                                    if ($value != '') {
                                        $total_sal += $value;
                                    }
                                }
                            }
                            //End
                            //debug($row); 
                            //edited by sinsiya on 03-04-2025                    
                            unset($row['']);
                            //debug($row);

                            foreach ($row as $key => $value) {
                                # code...
                                //debug($key);
                                if (!in_array($key, array("Employee ID", "Company ID", "Employee Name", "Monthly Gross Salary"))) { //Edited by Akshay on 14-10-2024

                                    //if ($value) {
                                    //edited by megha on 23/07/2025
                                    $salComponents = $this->EmployeeSalaryStructure->query("select distinct salary_head_item_fkey from emp_salary_structure ed WHERE TRIM(salary_head_item_desc) = '" . $key . "' and end_date_effective is null LIMIT 1 ");
                                    //debug($salComponents);
                                    $head_fkey = isset($salComponents[0]['ed']['salary_head_item_fkey']) ? $salComponents[0]['ed']['salary_head_item_fkey'] : 1000;
                                    //debug($salComponents[0]['ed']['salary_head_item_fkey']);
                                    // debug("select salary_head_item_fkey from emp_salary_structure ed WHERE TRIM(salary_head_item_desc) = '" . $key . "' LIMIT 1");
                                    // if($head_fkey != ''){
                                    $arr_empctc_data = array();
                                    $arr_empctc_data['emp_id'] = $emp_id;
                                    $arr_empctc_data['component'] = $key;
                                    $user_ids = $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                                    $arr_empctc_data['salary_head_item_fkey'] = isset($head_fkey) ? $head_fkey : 1000;
                                    if ($value == '') {
                                        $arr_empctc_data['rate'] = 0;
                                    } else {
                                        $arr_empctc_data['rate'] = $value;
                                    }
                                    //Edited by Akshay on 14-10-2024
                                    // edited by sinsiya on 04-04-2025
                                    if ($monthly_gross_sal == $total_sal && $monthly_gross_sal != 0 && $total_sal != 0) {
                                        //  debug($monthly_gross_sal);debug($total_sal);
                                        $result1 = $this->EmpSalaryCompUpload->saveAll($arr_empctc_data);
                                        //debug($result1); 
                                    } else {
                                        $unbalace_sal = true;
                                        $emp_company_id = isset($row['Company ID']) ? $row['Company ID'] : '';
                                        $emp_name = isset($row['Employee Name']) ? $row['Employee Name'] : '';
                                        if ($total_sal != 0) {
                                            $arr_rejected_emps[$emp] = trim($emp_name) . ' - ' . $emp_company_id;
                                        }
                                    }
                                    // End
                                    //}
                                }
                            }

                            try {
                                //****structure changes by megha adding distribution of salary on 20/04/2022****
                                $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
                                $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                                    'fields' => 'emp_pkey',
                                    'conditions' => array(
                                        'emp_id' => $emp_id
                                    )
                                ));
                                $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';
                                $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('$emp', @pmessage);");
                                $salary = $this->EmployeeSalaryStructure->find(
                                    "all",
                                    array(
                                        'fields' => 'emp_structure_id',
                                        'conditions' => array(
                                            'emp_fkey' => $emp,
                                            'end_date_effective is null'
                                        )
                                    )
                                );

                                $company = $this->Session->read('company_code');
                                $user_ids = $this->Session->read('login_user_id');

                                $salary_id = isset($salary['0']['EmployeeSalaryStructure']['emp_structure_id']) ? $salary['0']['EmployeeSalaryStructure']['emp_structure_id'] : "''";
                                $proc = $this->EmployeeSalaryStructure->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                                $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                                    "all",
                                    array(
                                        'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                                        'conditions' => array(
                                            'emp_structure_id' => $salary_id,
                                            'emp_fkey' => $emp,
                                            'remarks IS NOT NULL',
                                            'end_date_effective is null'
                                        )
                                    )
                                );
                                foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                                    $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                                    $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                                    $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';
                                    $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';

                                    if (!empty($formula_from_remarks)) {
                                        //edited by sinsiya on 20-08-2025
                                        $components = $this->EmployeeSalaryStructure->find('list', array(
                                            'fields' => array('salary_head_item_desc', 'structure_det_value'),
                                            'conditions' => array(
                                                'emp_fkey' => $emp,
                                                'end_date_effective is null'
                                            )
                                        ));

                                        // Replace placeholders like ComponentName(...) with values
                                        $formula_from_remarks = preg_replace_callback(
                                            '/([A-Za-z_]+)(?:\((.*?)\))?/',
                                            function ($matches) use ($components) {
                                                $componentName = $matches[1]; // e.g. DearnessAllowance or SpecialAllowance
                                                if (isset($components[$componentName])) {
                                                    return $components[$componentName]; // replace with actual value
                                                }
                                                return 0; // default if not found
                                            },
                                            $formula_from_remarks
                                        );
                                        //end by sinsiya
                                        $salary_amount = '0';
                                        eval('$salary_amount = ' . $formula_from_remarks . ';');
                                        if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employer Contribution' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employee Contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                                            if ($head_operator == 'Deduction') {
                                                $salary_amount = ceil($salary_amount);
                                            } else {
                                                $salary_amount = round($salary_amount);
                                            }
                                        } else {
                                            $salary_amount = round($salary_amount);
                                        }
                                        if ($head_operator == 'Deduction') {
                                            $salary_amount *= -1;
                                        }
                                        $arr_emp_salary_slip_data = array(
                                            'EmployeeSalaryStructure.structure_det_value' => round($salary_amount)
                                        );
                                        $this->EmployeeSalaryStructure->updateAll(
                                            $arr_emp_salary_slip_data,
                                            array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                                        );
                                    }
                                }
                                $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
                            } catch (Exception $ex) {
                                //return false;
                            }
                        }
                    }

                    unlink($targetpath);
                    //Edited by Akshay on 14-10-2024 //edited by sinsiya on 04-04-2025
                    // debug($result1); edited by sinsiya on 01-09-2025
                    if (!$unbalace_sal && $result1 == true) {
                        if ($ctcuploadtype == 1) {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Component Upload imported successfully.'));
                            exit;
                        } else {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Component Upload imported successfully'));
                            exit;
                        }
                    } else {
                        $rejected_emps_string = implode(', ', $arr_rejected_emps);
                        // debug($arr_rejected_emps);
                        if (count($arr_rejected_emps) > 0) {
                            $message = 'Employee Component Upload unsuccessful for ' . $rejected_emps_string;
                        } else {
                            $message = 'Employee Component Upload unsuccessful.';
                        }
                        echo json_encode(array('success' => 0, 'msg' => "Employee Component Upload unsuccessful.", 'warning' => "$message"));
                        exit;
                    }
                    //End
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Component Upload import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Component Upload import failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts revision failed! '));
                    exit;
                }
            }
        } else {

            echo json_encode(array('success' => 0, 'msg' => 'Please select a month '));
            exit;

            exit;
        }
    }


    public function loadcomponents($emp_fkey = '')
    {
        $arr_request_data = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $dqlToFetchComponentHeads = "select DISTINCT(salary_head_item_desc) as salary_head_item_desc, salary_head_item_fkey from emp_details ed "
            . "join emp_salary_structure as ectc on(ed.emp_pkey = ectc.emp_fkey) "
            . "join salary_head_items  on(salary_head_items.salary_head_item_pkey = ectc.salary_head_item_fkey) "
            . "where ectc.head_operator='Addition' and ectc.item_part='Direct' and salary_head_items.head_fkey = 1 and (ectc.end_date_effective='0000-00-00' or ectc.end_date_effective is null) and ectc.emp_fkey = " . $emp_fkey . " order by salary_head_item_order1 ASC";
        $arr_salary_components = $this->EmployeeDetails->query($dqlToFetchComponentHeads);
        $this->set("arr_salary_components", $arr_salary_components);
    }
    public function jsonbranchemp($branch = 0, $structure = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != '0') {
            $branch_condition = "and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = " and emp_details.branch_code = emp_details.branch_code";
        }

        // Edited by Akshay on 11-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = "and emp_details.branch_code in ('$is_ho')";
            }
        }
        // End

        if ($q != null) {
            $q_condition = "and first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ";
        } else {
            $q_condition = " ";
        }
        if ($structure != 0) {
            $Str_cond = " and emp_proff.structure_id in ('$structure') ";
        } else {
            $Str_cond = " and emp_proff.structure_id = emp_proff.structure_id ";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) left join emp_salary_structure as ectc on (emp_details.emp_pkey = ectc.emp_fkey) where emp_details.status = 1 $branch_condition $Str_cond  $q_condition group by emp_details.emp_id ORDER BY emp_pkey DESC ");
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


    // Edited by Akshay on 25-4-2025
    public function component()
    {
        $arr_request_data = $this->request->data;
        // debug($arr_request_data);
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $arr_leavetypes = $this->SalaryHeadItems->query("select salaLeaveRequestsry_head_item_pkey,item from salary_head_items where head_fkey=6 and upper(value)='Y'");
        $arr_structure = $this->SalaryStructures->find("all", array("conditions" => array("structure_active" => 1)));
        $this->set("arr_structure", $arr_structure);
        //edited by sinsiya 26-02-2024 
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $conditions = array("status" => 1);
            $conditions = array("branch_code !=" => $branch);
            //$arr_branches = $this->Units->find("all", array("conditions" => $conditions));
            $arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions));
            // debug($arr_employees);
            // exit;
        } else {

            $arr_employees = $this->EmployeeDetails->find("all");
        }

        // Edited by Akshay on 10-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $conditions = array('status' => 1);
        $is_ho = 1;
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions = array('status' => 1, 'branch_code' => $is_ho);
            }
        }

        // Edited by Akshay on 13-3-2025
        elseif ($user_group == 2 && ($company_code == 'GAAR' || $company_code == 'HRBL')) {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $user_id = $this->Session->read("login_user_id"); //user id
            $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
            $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;

            if ($special_access != 1) {
                $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                $conditions['branch_code != '] = $directors_branch;
            }
        }
        // End

        $this->set("is_ho", $is_ho);
        $arr_branches = $this->Units->find("all", array("conditions" => $conditions));
        // End
        $this->set("arr_branches", $arr_branches);

        //debug($arr_employees);
        $this->set("arr_employees", $arr_employees);
    }

    public function listincrements()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbconfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;

        $count = $this->EmployeeCTC->query(
            "select count(esc.sal_pkey) from component_increment esc 
            left join salary_head_items on(salary_head_items.salary_head_item_pkey =esc.component) where esc.status=1"
        );

        $count = isset($count[0][0]['count(esc.sal_pkey)']) ? $count[0][0]['count(esc.sal_pkey)'] : 0;
        $arr_att = $this->EmployeeCTC->query(
            "select *,salary_head_items.item from component_increment esc 
             left join salary_head_items on(salary_head_items.salary_head_item_pkey =esc.component) where esc.status=1 
             ORDER BY esc.sal_pkey desc"
                . " limit $limit "
                . " offset $ofst "
        );

        $out = array();
        $resp_att = array();
        foreach ($arr_att as $key => $value) {
            $out['sal_pkey'] = isset($value['esc']['sal_pkey']) ? $value['esc']['sal_pkey'] : '';
            $out['component_fkey'] = isset($value['esc']['component']) ? $value['esc']['component'] : '';
            $out['component'] = isset($value['salary_head_items']['item']) ? $value['salary_head_items']['item'] : '';
            $out['hike'] = isset($value['esc']['hike']) ? $value['esc']['hike'] : '';
            $out['next_increment_date'] = isset($value['esc']['next_increment_date']) && $value['esc']['next_increment_date'] != ''
                ? date('d-m-Y', strtotime($value['esc']['next_increment_date']))
                : '';

            $out['with_effect_from'] = isset($value['esc']['with_effect_from']) && $value['esc']['with_effect_from'] != ''
                ? date('d-m-Y', strtotime($value['esc']['with_effect_from']))
                : '';
            $resp_att["rows"][$key] = $out;
            $resp_att["total"] = $count;
        }
        echo json_encode($resp_att);
    }

    public function itemIncrementForm()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 30-1-2025

        if ($company_code == 'GLET' || $company_code == 'KWMT') {
            $arr_salary_head_items =  $this->EmployeeCTC->query("SELECT salary_head_item_pkey, item 
            FROM salary_head_items
            WHERE salary_head_item_pkey IN (
                SELECT salary_head_item_pkey 
                FROM salary_head_items 
                WHERE head_fkey IN (1, 4, 10)
            )
            AND salary_head_item_pkey IN (
                SELECT salary_head_item_fkey 
                FROM tax_salary_components 
                WHERE tax_salary_components_name IN ('Basic', 'VDA')
            )
            ");
        } else {
            $arr_salary_head_items =  $this->EmployeeCTC->query("SELECT salary_head_item_pkey, item 
            FROM salary_head_items
            WHERE salary_head_item_pkey IN (
                SELECT salary_head_item_pkey 
                FROM salary_head_items 
                WHERE head_fkey IN (1, 4)
            )
            AND status = 1;
            ");
        }

        $this->set("arr_salary_head_items", $arr_salary_head_items);
        $this->render('item_increment');
    }

    public function saveItemIncrement()
    {
        try {
            $this->autoRender = FALSE;
            $this->response->type('json');
            $this->ComponentIncrement->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;

            $user_id = $this->Session->read("login_user_id");
            $arr_form_data['created_by'] = $user_id;
            $arr_form_data['created_date'] = date('Y-m-d');

            $this->ComponentIncrement->saveAll($arr_form_data);

            $latest = $this->ComponentIncrement->find('first', [
                'conditions' => ['ComponentIncrement.status' => 1],
                'order' => ['ComponentIncrement.sal_pkey DESC'],
                'fields' => ['ComponentIncrement.sal_pkey'],
                'recursive' => -1
            ]);

            $latestSalPkey = $latest['ComponentIncrement']['sal_pkey'];

            echo json_encode([
                'success' => true, // Used to check status in JS
                'message' => 'Salary increment saved successfully.', // Custom message
                'increment_id' => $latestSalPkey // Return the latest insert ID or anything else needed
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to save increment.'
            ]);
        }
    }

    public function componentAllocate($sal_fkey = 0)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $condition = array("emp_pkey not in (select emp_fkey from component_increment_allocate where sal_fkey=$sal_fkey and status=1)");
        $this->set("sal_fkey", $sal_fkey);

        // Run the query to get the emp_fkey values
        $emp_fkeys = $this->EmployeeDetails->query('SELECT DISTINCT emp_fkey FROM emp_salary_structure WHERE end_date_effective IS NULL');

        // Extract the emp_fkey values from the result
        $emp_fkey_values = array_map(function ($row) {
            return $row['emp_salary_structure']['emp_fkey'];
        }, $emp_fkeys);

        //The below code is to display only unallocated employees to the specified store in employee list. By ***ARUL P DAS on 17/1/2020
        // $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1, $condition)));
        $arr_employees = $this->EmployeeDetails->find('all', array(
            'conditions' => array(
                'EmployeeDetails.status' => 1,
                'EmployeeDetails.emp_pkey IN' => $emp_fkey_values
            ),
            'joins' => array(
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmpProff',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'EmpProff.emp_fkey = EmployeeDetails.emp_pkey'
                    )
                )
            ),
            'fields' => array(
                'EmployeeDetails.*',
                'EmpProff.emp_company_id'
            ),
            'order' => array('EmployeeDetails.first_name' => 'ASC')
        ));


        $this->set("arr_employees", $arr_employees);
        //query edited by ***ARUL P DAS on 17/1/2020
        $arr_employees_allocates = $this->EmployeeDetails->query("select distinct emp_fkey,emp_details.first_name,last_name from component_increment_allocate join emp_details on (emp_details.emp_pkey = component_increment_allocate.emp_fkey) where sal_fkey = '$sal_fkey' and component_increment_allocate.status = '1'");
        $this->set("arr_employees_allocates", $arr_employees_allocates);
    }

    public function saveAllocate()
    {
        try {
            $this->autoRender = FALSE;
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $this->layout = null;
            $result = array('success' => 0);
            $arr_form_data = $this->request->data;
            $user = $this->Session->read('login_user_id');
            // debug($arr_form_data);
            // exit;
            $arr_emps = $arr_form_data['emps'];
            $sal_fkey = $arr_form_data['sal_fkey'];

            $resp_att = array();
            foreach ($arr_emps as $emps) {
                $cnt = $this->EmployeeDetails->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emps' and status=1 ");
                $count = $cnt['0']['0']['count'];

                // if ($count == 0) {
                if (true) {
                    try {

                        $arr_emp_proff = $this->EmployeeDetails->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emps';");
                        $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                        $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                        $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                        $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                        $this->EmployeeDetails->query("
                                                        INSERT INTO component_increment_allocate (
                                                            sal_fkey,
                                                            emp_fkey,
                                                            joining_date,
                                                            designation_code,
                                                            dept_code,
                                                            branch_code,
                                                            created_by
                                                        ) VALUES (
                                                            '$sal_fkey',
                                                            '$emps',
                                                            '$joining_date',
                                                            '$designation_code',
                                                            '$dept_code',
                                                            '$branch_code',
                                                            '$user'
                                                        )
                                                    ");

                        $resp_att['success'] = 1;
                        $resp_att['msg'] = "Saved Successfully.";
                        echo json_encode($resp_att);
                    } catch (Exception $e) {
                        debug($e);
                        $resp_att['success'] = 0;
                        $resp_att['msg'] = "Saving failed.";
                        echo json_encode($resp_att);
                    }
                } else {
                    $resp_att['success'] = 0;
                    $resp_att['msg'] = "Employee already exists.";
                    echo json_encode($resp_att);
                }
            }
        } catch (Exception $e) {
            debug($e);
        }
    }

    public function removeAllocate()
    {
        $this->autoRender = FALSE;
        // $this->Store->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;

        $emps = $arr_form_data['emps'];
        $sal_fkey = $arr_form_data['sal_fkey'];

        $resp_att = array();
        try {
            $this->EmployeeDetails->query("update component_increment_allocate set status = '0' where sal_fkey = '$sal_fkey' and emp_fkey = '$emps' ");
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Removed Successfully ";
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }

    public function getEmployeesByTypeValue()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $type = $this->request->data('type');
        $value = $this->request->data('value');

        // Mapping type to field name
        $map = [
            'Branch' => 'emp_branch',
            'Department' => 'emp_dept',
            'Designation' => 'designation',
            'Joining date' => 'joining_date',
            'Grade' => 'emp_grade',
            'emp_type' => 'emp_type'
        ];

        $field = isset($map[$type]) ? $map[$type] : null;
        if (!$field) {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }

        // Raw SQL Query to get employee details based on type and value
        $sql = "
            SELECT ed.emp_pkey, CONCAT(ed.first_name, ' ', ed.last_name, ' - ', ep.emp_company_id) AS name 
            FROM emp_details ed 
            INNER JOIN emp_proff ep ON ed.emp_pkey = ep.emp_fkey 
            WHERE ep.`$field` = '$value'
            ORDER BY ed.first_name ASC;
        ";

        // Using query method to execute the SQL
        $results = $this->EmployeeDetails->query($sql);

        // Mapping the results to the required format
        $employees = [];
        foreach ($results as $row) {
            // Ensure the correct access to the columns
            $employees[] = ['emp_pkey' => $row['ed']['emp_pkey'], 'name' => $row['0']['name']];
        }

        // Returning the result as a JSON response
        echo json_encode(['success' => true, 'data' => $employees]);
    }




    public function getTypeValues()
    {
        $this->autoRender = false;
        $type = $this->request->data('type');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $data = [];

        switch ($type) {
            case 'Branch':
                $sql = "SELECT DISTINCT branch_name as selected_value, branch_code as key_value FROM branches selcted_type WHERE status = 1 ORDER BY branch_name";
                break;
            case 'Department':
                $sql = "SELECT DISTINCT dept_name as selected_value , dept_code as key_value FROM department selcted_type WHERE status = 1 ORDER BY dept_name";
                break;
            case 'Designation':
                $sql = "SELECT DISTINCT desig_name as selected_value, desig_code as key_value FROM designation selcted_type WHERE status = 1 ORDER BY desig_name";
                break;
            case 'Joining date':
                $sql = "SELECT DISTINCT joining_date as selected_value FROM emp_proff selcted_type WHERE joining_date IS NOT NULL ORDER BY joining_date";
                break;
            case 'Grade':
                $sql = "SELECT DISTINCT grade_pkey as key_value, CONCAT(grade_name, ' - ' ,pay_scale) as selected_value FROM grade selcted_type WHERE status = 1 ORDER BY grade_name";
                break;
            case 'emp_type':
                $sql = "SELECT DISTINCT emp_type as key_value, emp_type as selected_value FROM emp_proff selcted_type ORDER BY emp_type";
                break;
            default:
                echo json_encode(['success' => false, 'data' => []]);
                return;
        }

        $results = $this->EmployeeDetails->query($sql);
        foreach ($results as $row) {
            // debug($row);
            $value = isset($row['selcted_type']['selected_value']) ? $row['selcted_type']['selected_value'] : (isset($row[0]['selected_value']) ? $row[0]['selected_value'] : ''); // get the first column
            if ($type === 'Joining date') {
                $key = date('Y-m-d', strtotime($value));
                $data[$key] = $type === 'Joining date' ? date('d-m-Y', strtotime($value)) : $value;
            } else {
                $key = $row['selcted_type']['key_value'];
                $data[$key] = $value;
            }
        }

        echo json_encode(['success' => !empty($data), 'data' => $data]);
    }



    public function saveComponentAllocate()
    {
        try {
            $this->autoRender = FALSE;
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            $this->layout = null;
            $result = array('success' => 0);
            $company = $this->Session->read('company_code');
            $user_id = $this->Session->read("login_user_id"); //user id
            $arr_form_data = $this->request->data;
            // debug($arr_form_data);exit;
            $arr_emps = $arr_form_data['emps'];
            $sal_fkey = $arr_form_data['sal_fkey'];
            $apply_to = $arr_form_data['apply_to'];
            $hike = $arr_form_data['hike'];
            $user = $this->Session->read('login_user_id');

            $response = array('success' => 0, 'msg' => 'Failed to allocate employees.');

            if (!empty($arr_emps)) {
                if ($apply_to == 'all') {

                    foreach ($arr_emps as $emps) {
                        // $this->EmployeeCTC->query("UPDATE component_increment_allocate SET status = 0 WHERE sal_fkey = '$sal_fkey'"); // set status to 0
                        // Allocate employees
                        $cnt = $this->EmployeeCTC->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emps' and status=1 ");
                        $count = $cnt['0']['0']['count'];

                        // if ($count == 0) {
                        if (true) {
                            try {

                                $arr_emp_proff = $this->EmployeeCTC->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emps';");
                                $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                                $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                                $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                                $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                                $this->EmployeeCTC->query("
                                                                INSERT INTO component_increment_allocate (
                                                                    sal_fkey,
                                                                    emp_fkey,
                                                                    joining_date,
                                                                    designation_code,
                                                                    dept_code,
                                                                    branch_code,
                                                                    created_by
                                                                ) VALUES (
                                                                    '$sal_fkey',
                                                                    '$emps',
                                                                    '$joining_date',
                                                                    '$designation_code',
                                                                    '$dept_code',
                                                                    '$branch_code',
                                                                    '$user'
                                                                )
                                                            ");
                            } catch (Exception $e) {
                                debug($e);
                            }
                        } else {
                        }


                        $arr_ctc =  $this->EmployeeCTC->query("SELECT emp_anual_ctc FROM emp_ctc_transaction WHERE emp_fkey = '$emps' AND end_date_effective IS NULL;");
                        $ctc = isset($arr_ctc[0]['emp_ctc_transaction']['emp_anual_ctc']) ? $arr_ctc[0]['emp_ctc_transaction']['emp_anual_ctc'] : '';
                        if ($ctc != '') {
                            $arr_update_data = array();
                            $arr_update_data['emp_fkey'] = $emps;
                            $arr_update_data['created_by'] = $user_id;
                            $arr_update_data['emp_anual_ctc'] = $new_value = round($ctc * (1 + ((float)$hike / 100)));
                            $arr_update_data['start_date_effective'] = date("Y-m-1");
                            $result = $this->EmployeeCTC->save($arr_update_data);

                            $arr_sal_id = $this->EmployeeCTC->query("SELECT emp_structure_id FROM emp_salary_structure ess WHERE emp_fkey = '$emps' AND end_date_effective IS NULL;");
                            $salary_id = isset($arr_sal_id[0]['ess']['emp_structure_id']) ? $arr_sal_id[0]['ess']['emp_structure_id'] : '';
                            // Edit salary strucure
                            $proc = $this->EmployeeCTC->query("select sal_structure_distribution_fn('$company',$emps,$salary_id,'$user_id') as function");
                            $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                                "all",
                                array(
                                    'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                                    'conditions' => array(
                                        'emp_structure_id' => $salary_id,
                                        'remarks IS NOT NULL',
                                        'emp_fkey' => $emps,
                                        'end_date_effective is null'
                                    )
                                )
                            );

                            foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                                $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                                $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                                $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';

                                $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';

                                if (!empty($formula_from_remarks)) {
                                    eval('$salary_amount = ' . $formula_from_remarks . ';');

                                    if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                                        if ($head_operator == 'Deduction') {
                                            $salary_amount = ceil($salary_amount); //Edited by Akshay on 30-4-2024
                                        } else {
                                            $salary_amount = round($salary_amount); //Edited by Akshay on 30-4-2024
                                        }


                                        if ($head_operator == 'Deduction') {
                                            $salary_amount *= -1;
                                        }

                                        $arr_emp_salary_slip_data = array(
                                            'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                                        );
                                    } else {
                                        $salary_amount = round($salary_amount);
                                        if ($head_operator == 'Deduction') {
                                            $salary_amount *= -1;
                                        }
                                        $arr_emp_salary_slip_data = array(
                                            'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                                        );
                                    }
                                    //  debug($arr_emp_salary_slip_data);
                                    $this->EmployeeSalaryStructure->updateAll(
                                        $arr_emp_salary_slip_data,
                                        array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                                    );
                                }
                            }

                            // ================================

                            $lastInsertId = $this->EmployeeCTC->query("SELECT `index` FROM emp_alteration WHERE affected_emp = '$emps' AND is_new = 'Y';");
                            $lastInsertId = isset($lastInsertId[0]['emp_alteration']['index']) ? $lastInsertId[0]['emp_alteration']['index'] : 0;
                            if ($lastInsertId != 0) {
                                $sql = "INSERT INTO emp_alteration_details (`index`,table_name,front_end_name, field, old_value, new_value, affected_emp, created_by, menu, module) 
                                VALUES ('$lastInsertId','emp_ctc_transaction','Annual Salary', 'emp_anual_ctc', '', '$new_value', '$emps', '$user_id', 'Employee setup', 'Add Employee')";
                                $result = $this->EmployeeCTC->query($sql);
                            }
                        }
                    }
                }
                $response = array('success' => 1, 'msg' => 'Employees allocated successfully.');
            } else {
                $response['msg'] = 'No employees selected for allocation.';
            }
        } catch (Exception $e) {
            debug($e);
            $response['msg'] = 'Error: ' . $e->getMessage();
        }
        echo json_encode($response);
        exit;
    }

    public function saveComponentUploads()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_emps = $arr_form_data['emps'];
        $salary_head_item_fkey = $arr_form_data['component'];
        $hike = $arr_form_data['hike'];
        $sal_fkey = $arr_form_data['sal_fkey'];
        $user = $this->Session->read('login_user_id');
        $company = strtoupper($this->Session->read('company_code'));

        $arr_salary_head_item_fkey = $this->EmployeeDetails->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Basic';");
        $basic = $arr_salary_head_item_fkey[0]['tax_salary_components']['salary_head_item_Fkey'];

        $arr_vda = $this->EmployeeDetails->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'VDA';");
        $vda = isset($arr_vda[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_vda[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        $arr_hra = $this->EmployeeDetails->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'House Rent Allowance (HRA)';");
        $hra = isset($arr_hra[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_hra[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        foreach ($arr_emps as $emp_key) {
            // Insert into component upload
            // $this->EmployeeDetails->query("UPDATE component_increment_allocate SET status = 0 WHERE sal_fkey = '$sal_fkey'"); // set status to 0
            // Allocate employees
            $cnt = $this->EmployeeDetails->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emp_key' and status=1 ");
            $count = $cnt['0']['0']['count'];

            if (true) {
                try {

                    $arr_emp_proff = $this->EmployeeDetails->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emp_key';");
                    $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                    $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                    $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                    $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                    $this->EmployeeDetails->query("
                                                    INSERT INTO component_increment_allocate (
                                                        sal_fkey,
                                                        emp_fkey,
                                                        joining_date,
                                                        designation_code,
                                                        dept_code,
                                                        branch_code,
                                                        created_by
                                                    ) VALUES (
                                                        '$sal_fkey',
                                                        '$emp_key',
                                                        '$joining_date',
                                                        '$designation_code',
                                                        '$dept_code',
                                                        '$branch_code',
                                                        '$user'
                                                    )
                                                ");
                } catch (Exception $e) {
                    debug($e);
                }
            } else {
            }

            $data = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_key");
            $emp_id = $data['0']['emp_details']['emp_id'];

            $arr_structure_det_value = $this->EmployeeDetails->query("SELECT structure_det_value FROM emp_salary_structure 
                                            WHERE emp_fkey = '$emp_key'
                                            AND salary_head_item_fkey = '$salary_head_item_fkey'
                                            AND end_date_effective IS NULL;
                                            ");
            $value =  isset($arr_structure_det_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_structure_det_value[0]['emp_salary_structure']['structure_det_value'] : '';

            $item = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $salary_head_item_fkey");
            $s_item = $item['0']['salary_head_items']['item'];

            $out['emp_id'] = $emp_id;
            $out['salary_head_item_fkey'] = $salary_head_item_fkey;
            if ($value == '') {
                $out['rate'] = 0;
            } else {
                if (($company == 'KWMT' || $company == 'GLET' || $company == 'GTRA') && ($salary_head_item_fkey == $vda || $salary_head_item_fkey == $hra)) { // Edited by Akshay on 24-9-2025
                    $arr_basic_value = $this->EmployeeDetails->query("SELECT structure_det_value FROM emp_salary_structure 
                                                                                    WHERE emp_fkey = '$emp_key'
                                                                                    AND salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Basic')
                                                                                    AND end_date_effective IS NULL;
                                                                                    ");
                    $basic_value = isset($arr_basic_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_basic_value[0]['emp_salary_structure']['structure_det_value'] : '';

                    // $value = round($basic_value * (1 + ((float)$hike / 100)));
                    $value = round($basic_value * ((float)$hike / 100));
                } else {
                    $value = round($value * (1 + ((float)$hike / 100)));
                }

                if (($company == 'KWMT' || $company == 'GLET' || $company == 'GTRA') && $salary_head_item_fkey == $basic) { // Edited by Akshay on 24-9-2025
                    if ($value  % 10 !== 0) {
                        $value = ceil($value / 10) * 10;
                    }
                }

                $out['rate'] = $value;
            }
            $out['component'] = $s_item;
            $out['created_by'] = $this->Session->read('login_user_id');

            $this->EmpSalaryCompUpload->query("
                    UPDATE emp_salcomp_upload 
                    SET status = 0 
                    WHERE emp_id = '$emp_id' 
                    AND salary_head_item_fkey = '$salary_head_item_fkey' 
                    AND status = 1
                ");

            $result = $this->EmpSalaryCompUpload->saveAll($out);



            //****structure changes by megha adding distribution of salary on 20/04/2022****
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                'fields' => 'emp_pkey',
                'conditions' => array(
                    'emp_id' => $emp_id
                )
            ));

            $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';

            $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('$emp', @pmessage);");
            // debug($arr_process);
            $salary = $this->EmployeeSalaryStructure->find(
                "all",
                array(
                    'fields' => 'emp_structure_id',
                    'conditions' => array(
                        'emp_fkey' => $emp,
                        'end_date_effective is null'
                    )
                )
            );

            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');

            $salary_id = isset($salary['0']['EmployeeSalaryStructure']['emp_structure_id']) ? $salary['0']['EmployeeSalaryStructure']['emp_structure_id'] : "''";
            // $proc = $this->EmployeeSalaryStructure->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
            $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                "all",
                array(
                    'fields' => 'emp_salary_structure_pkey,head_operator,remarks',
                    'conditions' => array(
                        'emp_structure_id' => $salary_id,
                        'emp_fkey' => $emp,
                        'remarks IS NOT NULL',
                        'end_date_effective is null'
                    )
                )
            );
            foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';
                if (!empty($formula_from_remarks)) {
                    $salary_amount = '0';
                    eval('$salary_amount = ' . $formula_from_remarks . ';');
                    if ($head_operator == 'Deduction') {
                        $salary_amount *= -1;
                    }
                    $arr_emp_salary_slip_data = array(
                        'EmployeeSalaryStructure.structure_det_value' => round($salary_amount)
                    );
                    $this->EmployeeSalaryStructure->updateAll(
                        $arr_emp_salary_slip_data,
                        array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                    );
                }
            }
            $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
        }

        $resp = array();
        $resp["success"] = 1;
        $resp["msg"] = "Salary component saved successfully.";
        echo json_encode($resp);
    }
    public function saveComponentAllocateKWMT()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_emps = $arr_form_data['emps'];
        $salary_head_item_fkey = $arr_form_data['component'];
        $hike = $arr_form_data['hike'];
        $sal_fkey = $arr_form_data['sal_fkey'];
        $user = $this->Session->read('login_user_id');
        $company = strtoupper($this->Session->read('company_code'));
        //edited by megha on 30_07_2025
        $arr_vda_hike = $this->EmployeeCTC->query("SELECT hike FROM component_increment WHERE sal_pkey = $sal_fkey;");
        $vda_hike = isset($arr_vda_hike[0]['component_increment']['hike']) ? $arr_vda_hike[0]['component_increment']['hike'] / 100 : '';

        $arr_salary_head_item_fkey = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Basic';");
        $basic = $arr_salary_head_item_fkey[0]['tax_salary_components']['salary_head_item_Fkey'];
        $basic_value = 0;

        $arr_hra = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'House Rent Allowance (HRA)';");
        $hra = isset($arr_hra[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_hra[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        if ($company == 'KWMT') {
            $arr_vda = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'VDA';");
        } else {
            $arr_vda = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Dearness Allowance (DA)';");
        }

        $vda = isset($arr_vda[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_vda[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        $response = array('success' => 0, 'msg' => 'Failed to allocate employees.');

        if (!empty($arr_emps)) {
            foreach ($arr_emps as $emp_key) {
                // insert into component allocate
                // $this->EmployeeDetails->query("UPDATE component_increment_allocate SET status = 0 WHERE sal_fkey = '$sal_fkey'"); // set status to 0
                // Allocate employees
                $cnt = $this->EmployeeDetails->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emp_key' and status=1 ");
                $count = $cnt['0']['0']['count'];

                // if ($count == 0) {
                if (true) {
                    try {

                        $arr_emp_proff = $this->EmployeeDetails->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emp_key';");
                        $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                        $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                        $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                        $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                        $this->EmployeeDetails->query("
                                                        INSERT INTO component_increment_allocate (
                                                            sal_fkey,
                                                            emp_fkey,
                                                            joining_date,
                                                            designation_code,
                                                            dept_code,
                                                            branch_code,
                                                            created_by
                                                        ) VALUES (
                                                            '$sal_fkey',
                                                            '$emp_key',
                                                            '$joining_date',
                                                            '$designation_code',
                                                            '$dept_code',
                                                            '$branch_code',
                                                            '$user'
                                                        )
                                                    ");
                    } catch (Exception $e) {
                        debug($e);
                    }
                } else {
                }

                $data = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_key");
                $emp_id = $data['0']['emp_details']['emp_id'];

                $arr_items =  $this->EmployeeCTC->query("SELECT * FROM emp_salary_structure
                                                            WHERE emp_fkey = '$emp_key'
                                                            AND end_date_effective IS NULL
                                                            AND head_operator = 'Addition'
                                                            -- AND item_part = 'Direct'
                                                            AND remarks IS NULL
                                                            ORDER BY salary_head_item_fkey ASC
                                                            ;");
                // debug($arr_items);
                $ctc = 0;
                $basic_value = 0;
                foreach ($arr_items as $items) {
                    $salary_head_item_fkey = $items['emp_salary_structure']['salary_head_item_fkey'];
                    // $arr_structure_det_value = $this->EmployeeDetails->query("SELECT structure_det_value FROM emp_salary_structure 
                    //                                                                 WHERE emp_fkey = '$emp_key'
                    //                                                                 AND salary_head_item_fkey = '$salary_head_item_fkey'
                    //                                                                 AND end_date_effective IS NULL;
                    //                                                                 ");
                    $value = isset($items['emp_salary_structure']['structure_det_value']) ? $items['emp_salary_structure']['structure_det_value'] : 0;
                    // $value =  isset($arr_structure_det_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_structure_det_value[0]['emp_salary_structure']['structure_det_value'] : '';

                    $item = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $salary_head_item_fkey");
                    $s_item = $item['0']['salary_head_items']['item'];

                    $out['emp_id'] = $emp_id;
                    $out['salary_head_item_fkey'] = $salary_head_item_fkey;
                    if ($value == '') {
                        $out['rate'] = 0;
                    } else {
                        if ($salary_head_item_fkey == $basic) {
                            $value = round($value * (1 + ((float)$hike / 100)));
                            // If $value is not a multiple of 10, round it up to the next multiple of 10
                            if ($value % 10 !== 0) {
                                $value = ceil($value / 10) * 10;
                            }
                            $basic_value = $value;
                        } elseif ($salary_head_item_fkey == $hra) {
                            $value = $basic_value * (.2);
                        } elseif ($salary_head_item_fkey == $vda) {
                            //$value = $basic_value * (2.285);
                            $value = $basic_value * $vda_hike;
                        }


                        $out['rate'] = $value;
                    }
                    $out['component'] = $s_item;
                    $out['created_by'] = $this->Session->read('login_user_id');

                    $this->EmpSalaryCompUpload->query("
                    UPDATE emp_salcomp_upload 
                    SET status = 0 
                    WHERE emp_id = '$emp_id' 
                    AND salary_head_item_fkey = '$salary_head_item_fkey' 
                    AND status = 1
                    ");

                    $result = $this->EmpSalaryCompUpload->saveAll($out);



                    //****structure changes by megha adding distribution of salary on 20/04/2022****
                    $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
                    $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                        'fields' => 'emp_pkey',
                        'conditions' => array(
                            'emp_id' => $emp_id
                        )
                    ));

                    $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';

                    $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('$emp', @pmessage);");
                    // debug($arr_process);
                    $salary = $this->EmployeeSalaryStructure->find(
                        "all",
                        array(
                            'fields' => 'emp_structure_id',
                            'conditions' => array(
                                'emp_fkey' => $emp,
                                'end_date_effective is null'
                            )
                        )
                    );

                    $company = $this->Session->read('company_code');
                    $user_ids = $this->Session->read('login_user_id');

                    $salary_id = isset($salary['0']['EmployeeSalaryStructure']['emp_structure_id']) ? $salary['0']['EmployeeSalaryStructure']['emp_structure_id'] : "''";
                    // $proc = $this->EmployeeSalaryStructure->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                    $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                        "all",
                        array(
                            'fields' => 'emp_salary_structure_pkey,head_operator,remarks',
                            'conditions' => array(
                                'emp_structure_id' => $salary_id,
                                'emp_fkey' => $emp,
                                'remarks IS NOT NULL',
                                'end_date_effective is null'
                            )
                        )
                    );

                    foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                        $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                        $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                        $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';
                        $key = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_fkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_fkey'] : '';
                        if (!empty($formula_from_remarks)) {
                            $salary_amount = '0';
                            eval('$salary_amount = ' . $formula_from_remarks . ';');
                            if ($head_operator == 'Deduction') {
                                $salary_amount *= -1;
                            }

                            if ($key == $basic) {
                                $salary_amount = round($salary_amount * (1 + ((float)$hike / 100)));
                                // If $value is not a multiple of 10, round it up to the next multiple of 10
                                if ($salary_amount % 10 !== 0) {
                                    $salary_amount = ceil($salary_amount / 10) * 10;
                                }
                                $basic_value = $salary_amount;
                            } elseif ($key == $hra) {
                                $salary_amount = $basic_value * (.2);
                            } elseif ($key == $vda) {
                                //$salary_amount = $basic_value * (2.285);
                                $salary_amount = $basic_value * $vda_hike;
                            }

                            $arr_emp_salary_slip_data = array(
                                'EmployeeSalaryStructure.structure_det_value' => round($salary_amount)
                            );
                            $this->EmployeeSalaryStructure->updateAll(
                                $arr_emp_salary_slip_data,
                                array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                            );
                        } else {
                            $salary_amount = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['structure_det_value']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['structure_det_value'] : 0;

                            if ($key == $basic) {
                                $salary_amount = round($salary_amount * (1 + ((float)$hike / 100)));
                                // If $value is not a multiple of 10, round it up to the next multiple of 10
                                if ($salary_amount % 10 !== 0) {
                                    $salary_amount = ceil($salary_amount / 10) * 10;
                                }
                                $basic_value = $salary_amount;
                            } elseif ($key == $hra) {
                                $salary_amount = $basic_value * (.2);
                            } elseif ($key == $vda) {
                                //$salary_amount = $basic_value * (2.285);
                                $salary_amount = $basic_value * $vda_hike;
                            }
                        }
                    }
                    $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
                }
            }
            $response = array('success' => 1, 'msg' => 'Employees allocated successfully.');
        } else {
            $response['msg'] = 'No employees selected for allocation.';
        }


        $resp = array();
        $resp["success"] = 1;
        $resp["msg"] = "Salary component saved successfully.";
        echo json_encode($resp);
    }
    // End
}
