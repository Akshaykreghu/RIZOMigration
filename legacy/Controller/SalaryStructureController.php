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
class SalaryStructureController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'SalaryStructure';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('SalaryHeadItems', 'Menu', 'SalaryHeads', 'SalaryStructures', 'SalaryStructureDetails', 'EmployeeProfessionalDetails', 'EmployeeConfig', 'EmployeeDetails');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index()
    {

        $this->layout = null;
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');

        $salaryHeadItemsDB = $this->SalaryHeadItems->find("all", array("conditions" => array("status" => 1, "value" => "Y")));

        $salaryStructureDB = $this->SalaryStructures->find("all", array("conditions" => array("structure_active " => 1)));
        $plan = $this->SalaryStructures->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        //debug($salaryStructureDB);exit;
        $this->set("salaryHeadItems", $salaryHeadItemsDB);
        $this->set("salaryStructure", $salaryStructureDB);
    }

    public function liststructures()
    {
        $this->layout = null;
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $conditions['structure_active'] = 1;
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;
        $count = $this->SalaryStructures->find("count", array("conditions" => $conditions));
        $salaryStructureDB = $this->SalaryStructures->find("all", array("conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        $structures = array();
        $structures["row"] = array();
        foreach ($salaryStructureDB as $key => $value) {
            $structures["rows"][$key] = $value["SalaryStructures"];
        }
        $structures["total"] = $count;
        echo json_encode($structures);
        $this->autoRender = FALSE;
    }

    public function addEmpToSalConfig()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $structure = (isset($_REQUEST['structure']) ? $_REQUEST['structure'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'SALARY';
                $data['emp_fkey'] = $value;
                $data['structure_id'] = $structure;


                $this->EmployeeConfig->save($data);
            }
        }
    }

    public function removeEmpFromSalConfig()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $structure = (isset($_REQUEST['structure']) ? $_REQUEST['structure'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'SALARY';
                $condition['emp_fkey'] = $value;

                $condition['structure_id'] = $structure;

                $this->EmployeeConfig->deleteAll($condition);
            }
        }
    }

    public function listemployeesinsalary()
    {

        $structure = (isset($_REQUEST['structure']) ? $_REQUEST['structure'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_in_struct = $this->EmployeeConfig->find("list", array("fields" => "emp_fkey", "conditions" => array("type" => "SALARY", "structure_id" => $structure)));

        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,Branches.branch_code';
        /*
          $joins = array( array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'))


          ); */

        $joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'),
        );
        $joins[] = array(
            'table' => 'branches',
            'alias' => 'Branches',
            'type' => 'LEFT',
            'conditions' => array(
                'EmployeeDetails.branch_code = Branches.branch_code'
            )
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_in_struct);

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        foreach ($arr_emp as $key => $value) {
            $resp_emp["emp"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0]);
            $resp_emp["emp"][$key]['branch_code'] = $value["Branches"]['branch_code'];
        }

        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesforsalary()
    {

        $structure = (isset($_REQUEST['structure']) ? $_REQUEST['structure'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_in_struct = $this->EmployeeConfig->find("list", array("fields" => "emp_fkey", "conditions" => array("type" => "SALARY", "structure_id" => $structure)));
        $db = $this->EmployeeConfig->getDataSource();
        //$conditions[] = $db->expression('status=1');
        //$this->User->find('all', compact('conditions'));
        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,Branches.branch_code';
        $joins = array(array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')));
        $conditions["EmployeeDetails.status"] = 1; //array('status' => 1, 'EmployeeDetails.emp_pkey NOT IN' => $emp_in_shift);
        if (!empty($emp_in_shift)) {
            $conditions["NOT"] = array('EmployeeDetails.emp_pkey' => $emp_in_struct);
        }
        $joins[] = array(
            'table' => 'branches',
            'alias' => 'Branches',
            'type' => 'LEFT',
            'conditions' => array(
                'EmployeeDetails.branch_code = Branches.branch_code'
            )
        );
        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        foreach ($arr_emp as $key => $value) {

            $resp_emp["emp"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0]);
            $resp_emp["emp"][$key]['branch_code'] = $value["Branches"]['branch_code'];
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function savesalarystructuresetup()
    {

        $this->autoRender = FALSE;
        $this->layout = null;

        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructureDetails->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;

        // Edited by Akshay on 11-2-2026
        // $specialCompanies = [
        //     'ABSG',
        //     'VGFS',
        //     'VSFS',
        //     'DRRC',
        //     'DJIC',
        //     'AGNG',
        //     'AYRK',
        //     'SHRD',
        //     'INFR',
        //     'ALDS',
        //     'CKWR',
        //     'BNGL',
        //     'RRLC',
        //     'DVDS',
        //     'STPN',
        //     'TSSM',
        //     'ELKT',
        //     'IMSC',
        //     'SNRY',
        //     'GTRA',
        //     'FRSG'
        // ];
        // End

        $specialCompanies = ['HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN','ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','SHYD','GTRA']; // Edited by Akshay on 23-3-2026

        $data = array();

        $data['structure_id'] = ($arr_form_data['structure_id']) ? $arr_form_data['structure_id'] : 0;
        $data['structure_name'] = $arr_form_data['structure_name'];
        $data['structure_eg_amt'] = $arr_form_data['structure_eg_amt'];
        $data['structure_created_date'] = strtotime('now');
        $data['structure_active'] = 1;
        $data['prorate_code'] = $arr_form_data['procode'];
        $data['prorate_desc'] = $arr_form_data['procode_desc'];
        $data['defined_structure_for'] = $arr_form_data['structure_define'];
        //$arr_date = explode("/", $arr_form_data['eff_satrt_date']);
        //$data['startdate_effective'] = $arr_date[2] . "-" . $arr_date[1] . "-" . $arr_date[0];
        //$arr_date = explode("/", $arr_form_data['eff_end_date']);
        //$data['enddate_effective'] = $arr_date[2] . "-" . $arr_date[1] . "-" . $arr_date[0];
        //$data['startdate_effective'] = $arr_form_data['eff_satrt_date'];
        //$data['enddate_effective'] = $arr_form_data['eff_end_date'];        
        $data['company_code'] = $company_code = $this->Session->read('company_code'); // Edited by Akshay on 21-1-2026
        $item_count = isset($arr_form_data['item_count']) ? $arr_form_data['item_count'] : 0;

        // Edited by Akshay on 21-1-2026
        if (!in_array($company_code, $specialCompanies)) {
            $data['fixed_days'] = isset($arr_form_data['fixed_days']) ? $arr_form_data['fixed_days'] : 0;
        }
        // End

        $this->SalaryStructures->save($data);
        $structure_id = ($arr_form_data['structure_id']) ? $arr_form_data['structure_id'] : $this->SalaryStructures->getLastInsertId();

        if ($structure_id) {
            $this->SalaryStructureDetails->deleteAll(array('SalaryStructureDetails.structure_id' => $arr_form_data['structure_id']), false);
        }
        $data = array();
        $data['structure_id'] = $structure_id;
        for ($i = 1; $i <= $arr_form_data['item_count']; $i++) {
            if (isset($arr_form_data["structure_det_value_" . $i]) && $arr_form_data["structure_det_value_" . $i] != "") {
                $data['structure_det_id'] = 0;
                $data['salary_head_item_fkey'] = $arr_form_data["item_name_" . $i];

                if (isset($arr_form_data["cal_equation_" . $i]) && $arr_form_data["cal_equation_" . $i] == 'rembalance') {
                    $data['structure_det_operator'] = 'rembalance';
                } else {
                    $data['structure_det_operator'] = $arr_form_data["structure_det_operator_" . $i];
                }

                //On 01 Aug 2016
                //Save formulae item for limit_wl or limit_wg
                if (isset($arr_form_data['radio_pickamount_' . $i]) && in_array($arr_form_data['radio_pickamount_' . $i], array('wl', 'wg'))) {
                    $data['structure_det_value'] = $arr_form_data["hid_formula_item_value_" . $i];
                } else {
                    $data['structure_det_value'] = $arr_form_data["structure_det_value_" . $i];
                }
                //Ends

                $data['structure_det_depends'] = 1;
                $data['structure_det_calequation'] = $arr_form_data["cal_equation_" . $i];
                $data['structure_formula'] = $arr_form_data["equation_" . $i];
                if ($data['structure_det_value'] != "")
                    $data['structure_derived_perc'] = $data['structure_det_value'] * 100 / $arr_form_data['structure_eg_amt'];
                else
                    $data['structure_derived_perc'] = "";
                if (isset($arr_form_data['radio_pickamount_' . $i]) && in_array($arr_form_data['radio_pickamount_' . $i], array('wl', 'wg'))) {
                    $data['structure_det_depends'] = $arr_form_data["hid_item_value_" . $i];
                } else {
                    $data['structure_det_depends'] = $arr_form_data["hid_item_value_to_save_" . $i];
                }
                $this->SalaryStructureDetails->save($data);
            }
        }
        $resp = array();
        $resp["success"] = true;
        $resp['msg'] = "Salary structure saved successfully";

        echo json_encode($resp);
    }

    public function loadSalaryStructureDetails($structure_id)
    {
        $this->layout = null;

        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructureDetails->useDbConfig = $this->Session->read('ds');
        $salaryStructureDB = $this->SalaryStructures->find("all", array("conditions" => array("structure_id" => $structure_id, "structure_active " => 1)));
        $salaryStructureDB = Set::extract('/SalaryStructures/.', $salaryStructureDB);
        $SalaryStructureDetailsDB = $this->SalaryStructureDetails->find("all", array(
            'fields' => 'SalaryStructureDetails.*,headitem.*',
            "conditions" => array("structure_id" => $structure_id),
            'joins' => array(
                array(
                    'table' => 'salary_head_items',
                    'alias' => 'headitem',
                    'type' => 'INNER',
                    'conditions' => array('SalaryStructureDetails.salary_head_item_fkey  = headitem.salary_head_item_pkey')
                )
            )
        ));
        $salaryStructureDB = $salaryStructureDB[0];
        $salaryStructureDB['salaryDetails'] = $SalaryStructureDetailsDB; //Set::extract('/SalaryStructureDetails/.', $SalaryStructureDetailsDB);
        $this->set("salaryHeadItems", $salaryStructureDB);

        $company_code = $this->Session->read('company_code');  // Edited by Akshay on 11-02-2026
        $this->set("company_code", $company_code); // Edited by Akshay on 21-01-2026
        // Edited by Akshay on 11-2-2026
        // $specialCompanies = [
        //     'ABSG',
        //     'VGFS',
        //     'VSFS',
        //     'DRRC',
        //     'DJIC',
        //     'AGNG',
        //     'AYRK',
        //     'SHRD',
        //     'INFR',
        //     'ALDS',
        //     'CKWR',
        //     'BNGL',
        //     'RRLC',
        //     'DVDS',
        //     'STPN',
        //     'TSSM',
        //     'ELKT',
        //     'IMSC',
        //     'SNRY',
        //     'GTRA',
        //     'FRSG'
        // ];

        $specialCompanies = ['HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN','ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','SHYD','GTRA']; // Edited by Akshay on 23-3-2026

        $this->set("fixed_days", !in_array($company_code, $specialCompanies));
        // End
        $this->render("form");
    }

    public function getSalaryHeadItems()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');

        $salaryHeadItemsDB = $this->SalaryHeadItems->find("all", array("conditions" => array("status" => 1, "value" => "Y")));
        return json_encode(Set::extract('/SalaryHeadItems/.', $salaryHeadItemsDB));
    }

    public function view($structure_id = 0)
    {
        $this->layout = null;
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructureDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $this->Menu->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-07-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        //end
        $arrayOfIds = ['fixed', 'limit'];
        if ($structure_id != 0) {
            $salaryLoadItems = $this->SalaryStructures->find("all", array("conditions" => array("structure_id" => $structure_id, "structure_active " => 1)));
            $salaryLoadItems = Set::extract('/SalaryStructures/.', $salaryLoadItems);
            $SalaryStructureDetailsDB = $this->SalaryStructureDetails->find("all", array(
                'fields' => 'SalaryStructureDetails.*,headitem.*',
                "conditions" => array("structure_id" => $structure_id),
                'joins' => array(
                    array(
                        'table' => 'salary_head_items',
                        'alias' => 'headitem',
                        'type' => 'INNER',
                        'conditions' => array('SalaryStructureDetails.salary_head_item_fkey  = headitem.salary_head_item_pkey')
                    )
                )
            ));
            $salaryLoadItems = $salaryLoadItems[0];
            $this->set("salaryLoadItems", $salaryLoadItems);
        }
        $respdata = array();
        $salaryHeadItemsDB = array();
        //edited by athira on 04-07-2025
        if ($plan == 'basic') {
            $salaryHeads = $this->SalaryHeads->find(
                "all",
                array(
                    "conditions" => array(
                        "status " => 1,
                        "plan" => 'basic',
                        'NOT' => array(
                            'SalaryHeads.head_occurance' => array('NA'),
                            'SalaryHeads.head_occurance' => array('LEAVE'),
                            'SalaryHeads.head_occurance' => array('VARIABLE')

                        )
                    )
                )
            );
        } else {
            $salaryHeads = $this->SalaryHeads->find(
                "all",
                array(
                    "conditions" => array(
                        "status " => 1,
                        'NOT' => array(
                            'SalaryHeads.head_occurance' => array('NA'),
                            'SalaryHeads.head_occurance' => array('LEAVE')
                        )
                    )
                )
            );
        }
        //end

        foreach ($salaryHeads as $key => $value) {
            $arr_salary_headItems = $this->SalaryHeadItems->find(
                "all",
                array(
                    "conditions" => array(
                        "status" => 1,
                        "value" => "Y",
                        "head_fkey" => $value['SalaryHeads']['head_pkey']
                    )
                )
            );
            $i = 0;
            foreach ($arr_salary_headItems as $salkey => $salvalue) {
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_name'] = $salvalue['SalaryHeadItems']['item'];
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_type'] = ($salvalue['SalaryHeadItems']['item_type'] != '') ? strtolower($salvalue['SalaryHeadItems']['item_type']) : "formula";
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_pkey'] = $salvalue['SalaryHeadItems']['salary_head_item_pkey'];

                if (isset($salvalue['SalaryHeadItems']['item_type']) && strtolower($salvalue['SalaryHeadItems']['item_type']) == 'fixed') {
                    $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_value'] = isset($salvalue['SalaryHeadItems']['item_value']) ? $salvalue['SalaryHeadItems']['item_value'] : '';
                } else {
                    $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_value'] = $this->calculateFixedLimit($salvalue['SalaryHeadItems']['occurance'], $salvalue['SalaryHeadItems']['item_value'], 1);
                }

                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['occurance'] = $salvalue['SalaryHeadItems']['occurance'];
                if ($structure_id != 0) {
                    $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['det'] = $this->search($SalaryStructureDetailsDB, 'salary_head_item_fkey', $salvalue['SalaryHeadItems']['salary_head_item_pkey']);
                }
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_head_operator'] = $value['SalaryHeads']['head_operator'];
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_head_occurance'] = $value['SalaryHeads']['head_occurance'];
                $i++;
            }
        }
        $this->set("salaryHeadItems", $salaryHeadItemsDB);
    }

    public function form($structure_id = 0)
    {
        $this->layout = null;
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructureDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        //edited by athira on 14-02-2025
        $this->Menu->useDbConfig = $this->Session->read('ds');
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        //end

        $company_code = $this->Session->read('company_code');
        $this->set("company_code", $company_code); // Edited by Akshay on 16-1-2026
        $arrayOfIds = ['fixed', 'limit'];
        if ($structure_id != 0) {
            $salaryLoadItems = $this->SalaryStructures->find("all", array("conditions" => array("structure_id" => $structure_id, "structure_active " => 1)));
            $salaryLoadItems = Set::extract('/SalaryStructures/.', $salaryLoadItems);
            $SalaryStructureDetailsDB = $this->SalaryStructureDetails->find("all", array(
                'fields' => 'SalaryStructureDetails.*,headitem.*',
                "conditions" => array("structure_id" => $structure_id),
                'joins' => array(
                    array(
                        'table' => 'salary_head_items',
                        'alias' => 'headitem',
                        'type' => 'INNER',
                        'conditions' => array('SalaryStructureDetails.salary_head_item_fkey  = headitem.salary_head_item_pkey')
                    )
                )
            ));
            $salaryLoadItems = $salaryLoadItems[0];
            $this->set("salaryLoadItems", $salaryLoadItems);
        }
        $respdata = array();
        $salaryHeadItemsDB = array();
        //edited by athira on 14-02-2025
        if ($plan == 'basic') {
            $salaryHeads = $this->SalaryHeads->find(
                "all",
                array(
                    "conditions" => array(
                        "status " => 1,
                        "plan" => 'basic',
                        'NOT' => array(
                            'SalaryHeads.head_occurance' => array('NA'),
                            'SalaryHeads.head_occurance' => array('LEAVE')
                        )
                    )
                )
            );
        } else {
            $salaryHeads = $this->SalaryHeads->find(
                "all",
                array(
                    "conditions" => array(
                        "status " => 1,
                        'NOT' => array(
                            'SalaryHeads.head_occurance' => array('NA'),
                            'SalaryHeads.head_occurance' => array('LEAVE')
                        )
                    )
                )
            );
        }
        //end

        foreach ($salaryHeads as $key => $value) {
            $arr_salary_headItems = $this->SalaryHeadItems->find(
                "all",
                array(
                    "conditions" => array(
                        "status" => 1,
                        "value" => "Y",
                        "head_fkey" => $value['SalaryHeads']['head_pkey']
                    )
                )
            );
            $i = 0;
            foreach ($arr_salary_headItems as $salkey => $salvalue) {
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_name'] = $salvalue['SalaryHeadItems']['item'];
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_type'] = ($salvalue['SalaryHeadItems']['item_type'] != '') ? strtolower($salvalue['SalaryHeadItems']['item_type']) : "formula";
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_pkey'] = $salvalue['SalaryHeadItems']['salary_head_item_pkey'];

                if (isset($salvalue['SalaryHeadItems']['item_type']) && strtolower($salvalue['SalaryHeadItems']['item_type']) == 'fixed') {
                    $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_value'] = isset($salvalue['SalaryHeadItems']['item_value']) ? $salvalue['SalaryHeadItems']['item_value'] : '';
                } else {
                    $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_value'] = $this->calculateFixedLimit($salvalue['SalaryHeadItems']['occurance'], $salvalue['SalaryHeadItems']['item_value'], 1);
                }

                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['occurance'] = $salvalue['SalaryHeadItems']['occurance'];
                if ($structure_id != 0) {
                    $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['det'] = $this->search($SalaryStructureDetailsDB, 'salary_head_item_fkey', $salvalue['SalaryHeadItems']['salary_head_item_pkey']);
                }
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_head_operator'] = $value['SalaryHeads']['head_operator'];
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_head_occurance'] = $value['SalaryHeads']['head_occurance'];

                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['item_part'] = $salvalue['SalaryHeadItems']['item_part'];
                $salaryHeadItemsDB[$value['SalaryHeads']['head_desc']][$i]['head_fkey'] = $salvalue['SalaryHeadItems']['head_fkey'];
                $i++;
            }
        }
        $this->set("salaryHeadItems", $salaryHeadItemsDB);

        // Edited by Akshay on 11-2-2026
        // $specialCompanies = [
        //     'ABSG',
        //     'VGFS',
        //     'VSFS',
        //     'DRRC',
        //     'DJIC',
        //     'AGNG',
        //     'AYRK',
        //     'SHRD',
        //     'INFR',
        //     'ALDS',
        //     'CKWR',
        //     'BNGL',
        //     'RRLC',
        //     'DVDS',
        //     'STPN',
        //     'TSSM',
        //     'ELKT',
        //     'IMSC',
        //     'SNRY',
        //     'GTRA',
        //     'FRSG'
        // ];

        $specialCompanies = ['HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN','ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','SHYD','GTRA']; // Edited by Akshay on 23-3-2026

        $this->set("fixed_days", !in_array($company_code, $specialCompanies));
        // End
    }


    public function calculateFixedLimit($occu, $amount, $structure_define)
    {
        if ($structure_define == 1) { //Monthly
            switch (strtolower($occu)) {
                case "monthly":
                    return $amount;
                    break;
                case "bimonthly":
                    return $amount * 2;
                    break;
                case "halfyearly":
                    return $amount / 6;
                    break;
                case "yearly":
                    return $amount / 12;
                    break;
                case "quarterly":
                    return $amount / 3;
                    break;
            }
        }
    }

    public function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search($subarray, $key, $value));
            }
        }

        return $results;
    }

    public function delete()
    {
        $this->autoRender = FALSE;
        $this->layout = null;

        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;


        $ar_id = explode(",", $_REQUEST['ids']);
        $data = array();


        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );

        $asiigned = $this->EmployeeProfessionalDetails->find("count", array("joins" => $joins, "conditions" => array('EmployeeProfessionalDetails.structure_id' => $ar_id, 'EmployeeDetails.status' => 1)));

        if ($asiigned > 0) {
            $resp = array();
            $resp["success"] = false;
            $resp["msg"] = "Salary structure cannot be deleted, remove employees under this Policy";
        } else {
            $this->SalaryStructures->updateAll(array('SalaryStructures.structure_active' => 0), array('SalaryStructures.structure_id' => $ar_id));
            $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.structure_id' => NULL), array('EmployeeProfessionalDetails.structure_id' => $ar_id));
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Salary structure deleted successfully";
        }
        echo json_encode($resp);
    }

    public function checkstructureexists($structure_id = 0)
    {
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;

        $structure_name = isset($arr_requestdata['structure_name']) ? $arr_requestdata['structure_name'] : '';

        $int_structurecount = 0;
        if ($structure_name != '') {
            $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
            $int_structurecount = $this->SalaryStructures->find(
                "count",
                array(
                    'conditions' => array('SalaryStructures.structure_name' => $structure_name, 'SalaryStructures.structure_active' => 1, 'SalaryStructures.structure_id != ' . $structure_id)
                )
            );
        }
        echo $int_structurecount;
    }
}
