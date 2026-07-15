<?php
/* include autoloader */
require_once '../Vendor/dompdf/autoload.inc.php';

/* reference the Dompdf namespace */

use Dompdf\Dompdf;

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

App::uses('ConnectionManager', 'Model', 'Organization');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class DocumentManagerController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'DocumentManager';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('DocTemplate', 'TemplatesDetails', 'Templates', 'Documents', 'EmpDetails', 'Units', 'EmployeeDetails', 'EmployeeExpenses', 'DocumentAllocation', 'DocumentUpload');
    public $components = array('MasterdataManagement');

    /*** Document Manager ***/
    public function index() {}
    function pagination($data)
    {
        $limit           = (isset($data['rows']) && !empty($data['rows'])) ? "  limit  " . $data['rows'] . "" : "";
        $offset          = (isset($data['page']) && !empty($data['page'])) ? " offset  " . (($data['page'] - 1)  * $data['rows']) . "" : "";
        return array("limit" => $limit, "offset" => $offset);
    }
    public function form($template_pkey = '')
    {

        if ($template_pkey != '') {
            $this->DocTemplate->useDbConfig = $this->Session->read('ds');
            $result = $this->DocTemplate->query('select template_name ,template_content,placeholders,availability, editable, policy from doc_template where  template_pkey=' . $template_pkey . ' and status = 1 ');

            if (isset($result[0]) && isset($result[0]['doc_template'])) {
                $this->set('template_name', (isset($result[0]['doc_template']['template_name']) ? $result[0]['doc_template']['template_name'] : ""));
                $this->set('template_content', (isset($result[0]['doc_template']['template_content']) ? $result[0]['doc_template']['template_content'] : ""));
                $this->set('placeholders', (isset($result[0]['doc_template']['placeholders']) ? $result[0]['doc_template']['placeholders'] : ""));
                //Edited by Akshay on 18-9-2023
                $this->set('policy', (isset($result[0]['doc_template']['policy']) ? $result[0]['doc_template']['policy'] : ""));
                $this->set('availability', (isset($result[0]['doc_template']['availability']) ? $result[0]['doc_template']['availability'] : ""));
                $this->set('editable', (isset($result[0]['doc_template']['editable']) ? $result[0]['doc_template']['editable'] : ""));
            }
        }
        $this->set('template_pkey', $template_pkey);
        $companycode = strtolower($this->Session->read('company_code'));
        $this->set('companycode', $companycode);
    }
    public function getTemplates($asArray = false)
    {
        $this->layout = '';
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $pageData = $this->pagination($_POST);
        $arr_data = $this->request->data;
        $temp = isset($arr_data['emp']) ? $arr_data['emp'] : '';
        if (isset($temp)) {
            $conditions = " and template_name like '%" . $temp . "%' ";
        } else {
            $conditions = "";
        }
        $result = $this->DocTemplate->query("SELECT template_pkey,template_name,modified_by,modification_date,created_by,creation_date FROM doc_template where status =1 $conditions order by template_pkey desc " . $pageData['limit'] . $pageData['offset']);
        $count = $this->DocTemplate->query("SELECT count(template_pkey)as count  FROM doc_template where status =1 $conditions");

        if ($asArray == true)
            return $result;
        $this->autoRender = false;
        $templates = array();
        foreach ($result as $key => $val) {
            if (isset($val['doc_template']['creation_date'])) {
                $val['doc_template']['creation_date'] = date('d-m-Y H:i:s', strtotime($val['doc_template']['creation_date']));
            }

            if (isset($val['doc_template']['modification_date'])) {
                $val['doc_template']['modification_date'] = date('d-m-Y H:i:s', strtotime($val['doc_template']['modification_date']));
            }

            $templates[] = $val['doc_template'];
        }

        $total_count = (isset($count[0]) && isset($count[0][0]) && isset($count[0][0]['count'])) ? $count[0][0]['count'] : 0;
        echo json_encode(array("total" => $total_count, "rows" => $templates));
    }
    public function saveTemplate()
    {
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $data = $_POST;
        $action = (isset($data['template_pkey']) && (!empty($data['template_pkey']))) ? 'edit' : 'add';
        if ($action == 'add') {
            $data['created_by'] = $loginUser;
            date_default_timezone_set('Asia/Kolkata');
            $data['creation_date'] = date('Y-m-d H:i:s');    //edited by Ashin on 30-10-24
            $this->DocTemplate->save($data);
            $result = $this->DocTemplate->getLastInsertID();
            if ($data['policy'] == 1) {
                $this->Documents->useDbConfig = $this->Session->read('ds');
                $data1['emp_fkey'] = 0;
                $data1['template_fkey'] = $result;
                $data1['company_fkey'] = 0;
                $data1['branch_fkey'] = 0;
                $data1['supplier_fkey'] = 0;
                $data1['customer_fkey'] = 0;
                $data1['others_fkey'] = 0;
                $template = $data['template_name'];
                $data1['document_name'] = $template . '_PolicyDocument';
                $data1['document'] = isset($data['template_content']) ? $data['template_content'] : '';
                $data1['created_by']    = $loginUser;
                date_default_timezone_set('Asia/Kolkata');
                $data1['creation_date'] = date('Y-m-d H:i:s');        //edited by Ashin 30-10-24
                $this->Documents->save($data1);
                $pkey = $this->Documents->getLastInsertID();
                $data1['doc_id'] = 'Doc_1000' . $pkey;
                $this->Documents->updateAll(array('Documents.doc_id' => "'" . $data1['doc_id'] . "'"), array('Documents.document_pkey' => $pkey));
            }
        } else {
            $data['modified_by'] = $loginUser;
            date_default_timezone_set('Asia/Kolkata');
            $data['modification_date'] = date('Y-m-d H:i:s');              //edited by Ashin 30-10-24
            //Edited by Akshay on 19-9-2023
            $data['policy'] = isset($data['policy']) ? $data['policy'] : 0;
            $data['availability'] = isset($data['availability']) ? $data['availability'] : 0;
            $data['editable'] = isset($data['editable']) ? $data['editable'] : 0;
            // $result = $this->DocTemplate->query('select template_name ,template_content,placeholders,availability, editable, policy from doc_template where  template_pkey=' . $template_pkey . ' and status = 1 ');
            $placeholder = isset($data['placeholders']) ? explode(',', $data['placeholders']) : [];
            $placeholder_type = isset($data["placeholder_type"]) ? explode(',', $data["placeholder_type"]) : [];
            if (!empty($placeholder_type)) {
                $placeholder_type = array_diff($placeholder_type, $placeholder);
                $updated_placeholders = array_unique(array_merge($placeholder, $placeholder_type));
            } else {
                $updated_placeholders = $placeholder;
            }
            // edited by anukrishnan_26-01-2025 open
            $existingRecords = $this->DocTemplate->query("SELECT placeholders FROM doc_template WHERE doc_template.template_pkey = " . $data['template_pkey']);
            $existingPlaceholders = isset($existingRecords[0]['doc_template']['placeholders']) ? $existingRecords[0]['doc_template']['placeholders'] : '';
            $existingPlaceholdersArray = !empty($existingPlaceholders) ? explode(',', $existingPlaceholders) : [];
            $data['placeholders'] = implode(',', $updated_placeholders);
            $newPlaceholdersArray = explode(',', $data['placeholders']); 
            $finalPlaceholdersArray = array_unique(array_merge($existingPlaceholdersArray, $newPlaceholdersArray));
            $data['placeholders'] = implode(',', $finalPlaceholdersArray);
            $this->DocTemplate->save($data, true, array('template_name', 'template_content', 'modified_by', 'modification_date', 'policy', 'availability', 'editable', 'placeholders'));
            // edited by anukrishnan_26-01-025 close
            // $this->DocTemplate->save($data, true, array('template_name', 'template_content', 'modified_by', 'modification_date', 'policy', 'availability', 'editable'));  //edited by anukrishnan 21-01-2025
        }
        echo json_encode(array('msg' => 'Document saved successfully.'));
    }
    public function deleteTemplate()
    {
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $data = array();
        $data['template_pkey']      = $_GET['ids'];
        $data['modified_by']        = $loginUser;
        $data['modification_date']  = date('d-m-Y H:i:s');        //edited by Ashin on 30-10-24
        $data['status']             = 0;
        $this->DocTemplate->save($data, true, array('modified_by', 'modification_date', 'status'));
        echo json_encode(array('msg' => 'Document deleted successfully.'));
    }
    public function createDocument() { 
        $companycode = strtolower($this->Session->read('company_code'));
        $this->set('companycode', $companycode);
    }
    public function addimage()
    {
        $companycode = strtolower($this->Session->read('company_code'));
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $images = $this->DocTemplate->query("SELECT image_url,image_pkey FROM doc_images where company_code = '$companycode' and status = 1 order by image_pkey desc limit 32");
        $this->set('images', $images);
    }
    public function deleteImage($pkey = 0)
    {
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $data = array();
        $template_pkey      = $pkey;
        $modified_by        = $loginUser;
        $modification_date  = date('d-m-Y H:i:s');         //edited by Ashin on 30-10-24
        $status             = 0;
        $this->DocTemplate->query("update doc_images set modified_by = '$modified_by',modification_date='$modification_date',status='$status' where image_pkey = '$template_pkey' ");
        echo json_encode(1);
    }
    public function getDocuments($asArray = false)
    {
        $this->layout = '';
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $companycode = strtolower($this->Session->read('company_code'));
        $this->set('companycode', $companycode);
        $pageData = $this->pagination($_POST);
        $userGroup = $this->Session->read('user_group');
        $user_id = $this->Session->read('emp_fkey');
        if ($userGroup == 2) {
            $cond = " and ( emp_fkey = '$user_id' or doc.policy = 1 ) ";
        } else {
            $cond = "";
        }
        $arr_data = $this->request->data;
        $temp = isset($arr_data['emp']) ? $arr_data['emp'] : '';
        if (isset($temp)) {
            $conditions = " and (document_name like '%" . $temp . "%' OR tem.template_name like '%" . $temp . "%'  OR first_name like '%" . $temp . "%' OR last_name like '%" . $temp . "%') ";
        } else {
            $conditions = "";
        }
        $result = $this->Documents->query('SELECT concat(COALESCE (em.first_name,"")," ",COALESCE(em.middile_name,"")," ",COALESCE(em.last_name,"")) as emp_name,template_name,document_name,
            template_fkey,emp_fkey,document_pkey,doc.created_by,doc.creation_date,doc.doc_id
            FROM `documents` as doc 
            LEFT JOIN emp_details as em on (em.emp_pkey = doc.emp_fkey and em.status in (1,2) ) 
            LEFT JOIN doc_template as tem on ( tem.template_pkey = doc.template_fkey and tem.status = 1 ) 
            where  doc.status = 1 ' . $cond . $conditions . ' order by document_pkey desc ' . $pageData['limit'] . $pageData['offset']);

        $count = $this->Documents->query("SELECT count(document_pkey) as count FROM `documents` as doc LEFT JOIN emp_details as em on (em.emp_pkey = doc.emp_fkey and em.status in (1,2) )  LEFT JOIN doc_template as tem on ( tem.template_pkey = doc.template_fkey and tem.status = 1 ) WHERE doc.status = 1 " . $cond . $conditions . " ");
        if ($asArray == true)
            return $result;
        $this->autoRender = false;
        $documents = array();
        foreach ($result as $key => $val) {
            if (isset($val['doc']['creation_date'])) {
                $val['doc']['creation_date'] = date('d-m-Y H:i:s', strtotime($val['doc']['creation_date']));
            }
            //edited by athira on 13-01-2025
            $url = Router::url('/', true) .  'DocumentManager/docPreview/' . $val['doc']['document_pkey'] . '/1';
            $downloadLink = '<a href="' . $url . '"> 
            <button type="button" class="btn btn-info" style="margin:5px 0;">
                <li class="fa fa-download"></li>
            </button>
            </a>';
            // Print button with FontAwesome icon
            // Edited by Akshay on 13-1-2025
            $printButton = "";
            if($companycode == 'absg' || $companycode == 'demo'){
            $printButton = '
            <button type="button" id="btn-submit4" class="btn btn-info" style="display: inline-block;margin:5px 0;" onclick="printPreview(\'' . $val['doc']['document_pkey'] . '\')">
                <li class="fa fa-print"></li>
            </button>';
            }
            // End
            $documents[] = array_merge(
                $val['tem'],
                $val[0],
                $val['doc'],
                array('download_link' => $downloadLink . ' ' . $printButton)
            );
            //$link = '<a href='.$url.'> '.$val['doc']['document_name'].'</a>';
            // $link = '<a href=' . $url . '> <button>Download</button> </a>';
            // $documents[] =  array_merge($val['tem'], $val[0], $val['doc'], array('download_link' => $link));
        }
        //end
        $total_count = (isset($count[0]) && isset($count[0][0]) && isset($count[0][0]['count'])) ? $count[0][0]['count'] : 0;
        echo json_encode(array("total" => $total_count, "rows" => $documents));
    }
    public function documentForm()
    {
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $userGroup = $this->Session->read('user_group');
        if ($userGroup == 2) {
            $cond = " and availability = 1 ";
        } else {
            $cond = "";
        }
        $templates = $this->DocTemplate->query('select template_pkey ,template_name, template_content from doc_template where status = 1 and policy = 0 ' . $cond);
        $this->set('templates', $templates);
        // $this->EmpDetails->useDbConfig = $this->Session->read('ds');
        // $employees = $this->EmpDetails->query('select emp_pkey,emp_id ,trim(concat(COALESCE (first_name,"")," ",COALESCE(middile_name,"")," ",COALESCE(last_name,""))) as emp_name from emp_details where status = 1 order by first_name');  
        // $this->set('employees',$employees);
    }
    public function getData($data = 0)
    {
        $this->layout = '';
        $this->autoRender = false;
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $userGroup = $this->Session->read('user_group');
        if ($userGroup == 2) {
            echo json_encode(array('status' => false));
        } else {
            $templates = $this->DocTemplate->query('select editable from doc_template where status = 1 and template_pkey = ' . $data);
            $result = $templates[0]['doc_template']['editable'];
            if ($result == 1) {
                echo json_encode(array('status' => true));
            } else {
                echo json_encode(array('status' => false));
            }
        }
    }
    /**
     *  preview ( 0 - no preview , 1 - pdf , 2 - html)
     */
    public function docPreview($document_id = '', $preview = 0)
    {
        $this->layout = '';
        $this->autoRender = false;
        $this->Documents->useDbConfig = $this->Session->read('ds');
         //edited by sinsiya 20-09-2024
         $companycode = strtolower($this->Session->read('company_code'));
        $file_name = 'Document.pdf';

        if (isset($document_id) && !empty($document_id)) {
            $data = $this->Documents->query("select document_name,document,creation_date,created_by,doc_id from documents where document_pkey = " . $document_id . "");
            $date = $data[0]['documents']['creation_date'];
            $created = $data[0]['documents']['created_by'];
            $html = (isset($data[0]) && isset($data[0]['documents']['document'])) ?  $data[0]['documents']['document'] : '';
            $doc = (isset($data[0]) && isset($data[0]['documents']['doc_id'])) ?  $data[0]['documents']['doc_id'] : '';
           //edited by sinsiya on 20-09-2024
           if($companycode !='ayrg'&& $companycode != 'demo' ){
            $html .= '<style>
            #footer { position: fixed; left: 0px; bottom: -30px; right: 0px; height:20px; text-align:center;padding:10px;font-size:12px; }
            </style>
            <div id="footer"> Generated by ' . $created . ' at ' . $date . ' with Document ID ' . $doc . '</div>';
            }
            $file_name = (isset($data[0]) && isset($data[0]['documents']['document_name'])) ?  $data[0]['documents']['document_name'] : $file_name;
            $template = $html;
            //  debug($template);die();
        } else {
            $emp_fkey       = (isset($_POST['emp_id'])       && !empty($_POST['emp_id']))       ? $_POST['emp_id'] : 0;
            $company_fkey   = (isset($_POST['company_id'])   && !empty($_POST['company_id']))   ? $_POST['company_id'] : 0;
            $branch_fkey    = (isset($_POST['branch_id'])    && !empty($_POST['branch_id']))    ? $_POST['branch_id'] : 0;
            $supplier_fkey  = (isset($_POST['supplier_id'])  && !empty($_POST['supplier_id']))  ? $_POST['supplier_id'] : 0;
            $customer_fkey  = (isset($_POST['customer_id'])  && !empty($_POST['customer_id']))  ? $_POST['customer_id'] : 0;
            $others_fkey    = (isset($_POST['others_id'])    && !empty($_POST['others_id']))    ? $_POST['others_id'] : 0;
            $template       = (isset($_POST['template'])     && !empty($_POST['template']))     ? $_POST['template'] : '';
            $placeholders   = (isset($_POST['placeholders']) && !empty($_POST['placeholders'])) ? $_POST['placeholders'] : array();

            $empolyee_name = $empolyee_image = $employee_currentdate = $employee_id = $blood_group = $issued_date = $emp_type = $designation = $department = $branch = $branchaddress = $joining_date  = $notice_days = $address = $city = $state = $pincode = $mobile_no = $email = $gender = $maritual_status = $education = $dob = $bank_name = $branch_name = $bank_address = $ifsc_code = $account_no = $ctc = $guardian = $yearly_ctc = '';
            $company_name = $company_nature = $company_type = $company_address = $company_city = $company_pincode = $company_state = $company_phone = $company_fax = $company_logo = '';
            $branch_name = $branch_address = $branch_city = $branch_state = $branch_pincode = $branch_email = '';
            $supplier_name = $supplier_comp_name = $supplier_email = $supplier_phone = $supplier_address = $supplier_city = $supplier_state = $supplier_pincode =  '';
            $customer_name = $customer_comp_name = $customer_email = $customer_phone = $customer_address = $customer_city = $customer_state = $customer_pincode = '';
            $others_name = $others_comp_name = $others_email = $others_phone = $others_address = $others_city = $others_state = $others_pincode = '';
            //Edited by Akshay on 15-7-2024
            $submitted_date = $last_applied_date = $last_working_date = $last_approved_working_date = $current_date = '';
            //End
            //Edited by Akshay on 15-7-2024
            $monthly_ctc = $duration = $salary_brkup = '';
            //End
            foreach ($placeholders as $placeholder) {
                switch ($placeholder) {
                    case 'emp':
                        if ($emp_fkey == 0) {
                            $empolyee_name  = '{:empolyee_name}';
                            $employee_id  = '{:employee_id}';
                            $emp_type  = '{:employee_emp_type}';
                            $designation  = '{:employee_designation}';
                            $department  = '{:employee_department}';
                            $branch  = '{:employee_branch}';
                            $branch_address  = '{:employee_branch_address}';
                            $joining_date  = '{:employee_joining_date}';
                            $notice_days  = '{:employee_notice_days}';
                            $address  = '{:employee_address}';
                            $city  = '{:employee_city}';
                            $state  = '{:employee_state}';
                            $pincode  = '{:employee_pincode}';
                            $mobile_no  = '{:employee_mobile_no}';
                            $email  = '{:employee_email}';
                            $gender  = '{:employee_gender}';
                            $maritual_status  = '{:employee_maritual_status}';
                            $guardian = '{:employee_guardian}';
                            $education  = '{:employee_education}';
                            $dob  = '{:employee_date_of_birth}';
                            $bank_name  = '{:employee_bank_name}';
                            $branch_name  = '{:employee_branch_name}';
                            $bank_address  = '{:employee_bank_address}';
                            $ifsc_code  = '{:employee_ifsc_code}';
                            $account_no  = '{:employee_account_no}';
                            $ctc  = '{:employee_ctc}';
                            $yearly_ctc  = '{:employee_yearly_ctc}';
                            //Edited by Akshay on 15-7-2024
                            $monthly_ctc = '{:monthly_ctc}';
                            $duration = '{:employee_duration}';
                            $salary_brkup = '{:salary_break_up}';
                            //End
                        } else {
                            $employe = $this->getEmployees($emp_fkey);
                            $employe = (isset($employe) && isset($employe[0])) ?  $employe[0] : array();

                            if (substr_count($template, '<p>{:employee_yearly_ctc}</p>') == 0 && substr_count($template, '<p>{:monthly_ctc}</p>') == 0 && substr_count($template, '{:salary_break_up}') == 0) {
                                $result1 = $this->Documents->query("select ectc.salary_head_item_desc,ectc.structure_det_value from emp_salary_structure as ectc left join termination as termination on (termination.emp_fkey = ectc.emp_fkey  and termination.status = 1 ) 
                                                                left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                                                where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '$emp_fkey'   and ectc.end_date_effective is null 
                                                                and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4)) 
                                                                group by ectc.salary_head_item_desc order by shead.salary_head_item_order1");
                                $html = "<table><tr><th>Salary Item</th><th>Amount</th></tr>";
                                $sum = 0;
                                foreach ($result1 as $key => $val) {
                                    $sum = $sum + $val['ectc']['structure_det_value'];
                                    $html .= '<tr><td>' . $val['ectc']['salary_head_item_desc'] . '</td><td>' . $val['ectc']['structure_det_value'] . '</td></tr>';
                                }
                                $html .= '<tr><td><b>Net Salary</b></td><td><b>' . round($sum) . '</b></td></tr></table>';
                            }
                            //Edited by Akshay on 15-7-2024
                            else if (substr_count($template, '{:salary_break_up}') != 0) {
                                $result1 = $this->Documents->query("select ectc.salary_head_item_desc,ectc.structure_det_value from emp_salary_structure as ectc left join termination as termination on (termination.emp_fkey = ectc.emp_fkey  and termination.status = 1 ) 
                                left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '$emp_fkey'   and ectc.end_date_effective is null 
                                and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4)) 
                                and ectc.item_part = 'Direct' 
                                group by ectc.salary_head_item_desc order by shead.salary_head_item_order1");
                                $html = "<table align='center' class='MsoTableGrid' style='border-collapse:collapse; border:solid windowtext 1.0pt'><tr><th colspan='2' style='border:solid windowtext 1.0pt; width:402.5pt; padding:0cm 5.4pt 0cm 5.4pt; height:22.7pt; text-align: center;' width='537'>Salary Break Up</th></tr>";
                                $sum2 = 0;
                                $sum = 0;
                                foreach ($result1 as $key => $val) {
                                    $sum = $sum + round($val['ectc']['structure_det_value']);
                                    $html .= '<tr style="height:22.7pt"><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt">' . $val['ectc']['salary_head_item_desc'] . '</td><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt">' . round($val['ectc']['structure_det_value']) . '</td></tr>';
                                }
                                $html .= '<tr style="height:22.7pt"><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt"><b>Gross</b></td><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt"><b>' . round($sum) . '</b></td></tr>';

                                $result2 = $this->Documents->query("select ectc.salary_head_item_desc,ectc.structure_det_value from emp_salary_structure as ectc left join termination as termination on (termination.emp_fkey = ectc.emp_fkey  and termination.status = 1 ) 
                                left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                where ectc.head_operator = 'Deduction' and ectc.emp_fkey = '$emp_fkey'   and ectc.end_date_effective is null 
                                and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey = 5)
                                and ectc.item_part = 'Direct' 
                                group by ectc.salary_head_item_desc order by shead.salary_head_item_order1");
                                foreach ($result2 as $key => $val) {
                                    $sum2 = $sum2 + round($val['ectc']['structure_det_value']);
                                    $html .= '<tr style="height:22.7pt"><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt">' . $val['ectc']['salary_head_item_desc'] . '</td><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt">' . abs(round($val['ectc']['structure_det_value'])) . '</td></tr>';
                                }
                                $html .= '<tr style="height:22.7pt"><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt"><b>Net Salary</b></td><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt"><b>' . (round($sum) + round($sum2)) . '</b></td></tr>';

                                $result3 = $this->Documents->query("select ectc.salary_head_item_desc,ectc.structure_det_value from emp_salary_structure as ectc left join termination as termination on (termination.emp_fkey = ectc.emp_fkey  and termination.status = 1 ) 
                                left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '$emp_fkey'   and ectc.end_date_effective is null 
                                and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4))
                                and ectc.item_part = 'Indirect' 
                                group by ectc.salary_head_item_desc order by shead.salary_head_item_order1");
                                foreach ($result3 as $key => $val) {
                                    $sum = $sum + round($val['ectc']['structure_det_value']);
                                    $html .= '<tr style="height:22.7pt"><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt">' . $val['ectc']['salary_head_item_desc'] . '</td><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt">' . round($val['ectc']['structure_det_value']) . '</td></tr>';
                                }
                                $html .= '<tr style="height:22.7pt"><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt"><b>Total CTC</b></td><td style="border:solid windowtext 1.0pt; width:233.75pt; padding:0cm 5.4pt 0cm 5.4pt"><b>' . round($sum) . '</b></td></tr></table>';
                            }
                            //End
                            else {
                                $result1 = $this->Documents->query("select ectc.salary_head_item_desc,ectc.structure_det_value from emp_salary_structure as ectc left join termination as termination on (termination.emp_fkey = ectc.emp_fkey  and termination.status = 1 ) 
                            left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                            where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '$emp_fkey' and ectc.item_part = 'Direct' and ectc.end_date_effective is null 
                            and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4)) 
                            group by ectc.salary_head_item_desc order by shead.salary_head_item_order1");
                                $result2 = $this->Documents->query("select ectc.salary_head_item_desc,ectc.structure_det_value from emp_salary_structure as ectc left join termination as termination on (termination.emp_fkey = ectc.emp_fkey  and termination.status = 1 ) 
                            left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                            where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '$emp_fkey' and ectc.item_part = 'Indirect' and ectc.end_date_effective is null 
                            and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4)) 
                            group by ectc.salary_head_item_desc order by shead.salary_head_item_order1");

                                $html = "<table style='border-collapse: collapse; width:100%;'><tr><th style='border: 1px solid black;padding-left:40px;'>Component</th><th style='border: 1px solid black;padding-left:40px;'>Monthly</th><th style='border: 1px solid black;padding-left:40px;'>Yearly</th></tr>";
                                $sum = 0;
                                $sum_y = 0;
                                $sum_d = 0;
                                $sum_y_d = 0;
                                $sum_i = 0;
                                $sum_y_i = 0;
                                foreach ($result1 as $key => $val) {
                                    $sum = $sum + $val['ectc']['structure_det_value'];
                                    $sum_y = $sum_y + ($val['ectc']['structure_det_value'] * 12);
                                    $sum_d = $sum_d + $val['ectc']['structure_det_value'];
                                    $sum_y_d = $sum_y_d + ($val['ectc']['structure_det_value'] * 12);
                                    $html .= '<tr><td style="border: 1px solid black; padding-left:40px;">' . $val['ectc']['salary_head_item_desc'] . '</td><td style="border: 1px solid black; padding-left:40px;">' . $val['ectc']['structure_det_value'] . '</td><td style="border: 1px solid black; padding-left:40px;">' . ($val['ectc']['structure_det_value'] * 12) . '</td></tr>';
                                }
                                if (true) {
                                    $html .= "<tr ><td style='border: 1px solid black; padding-left:40px;'><b>Gross Salary</b></td><td style='border: 1px solid black; padding-left:40px;'>" . $sum_d . "</td><td style='border: 1px solid black; padding-left:40px;'>" . $sum_y_d . "</td></tr>";
                                    $html .= '<tr><td style="border: 1px solid black"></td><td style="border: 1px solid black"></td><td style="border: 1px solid black"></td></tr>';
                                }

                                foreach ($result2 as $key => $val) {
                                    $sum = $sum + $val['ectc']['structure_det_value'];
                                    $sum_y = $sum_y + ($val['ectc']['structure_det_value'] * 12);
                                    $sum_i = $sum_i + $val['ectc']['structure_det_value'];
                                    $sum_y_i = $sum_y_i + ($val['ectc']['structure_det_value'] * 12);
                                    $html .= '<tr><td style="border: 1px solid black; padding-left:40px;">' . $val['ectc']['salary_head_item_desc'] . '</td><td style="border: 1px solid black; padding-left:40px;">' . $val['ectc']['structure_det_value'] . '</td><td style="border: 1px solid black; padding-left:40px;">' . ($val['ectc']['structure_det_value'] * 12) . '</td></tr>';
                                }
                                if (true) {
                                    $html .= '<tr><td style="border: 1px solid black; padding-left:40px;"><b>Gross Contributions</b></td><td style="border: 1px solid black; padding-left:40px;">' . $sum_i . '</td><td style="border: 1px solid black; padding-left:40px;">' . $sum_y_i . '</td></tr>';
                                    $html .= '<tr><td style="border: 1px solid black; padding-left:40px;"></td><td style="border: 1px solid black"></td><td style="border: 1px solid black"></td></tr>';
                                }
                                $html .= '<tr><td style="border: 1px solid black; padding-left:40px;"><b>COST TO COMPANY</b></td><td style="border: 1px solid black; padding-left:40px;">' . round($sum) . '</td><td style="border: 1px solid black; padding-left:40px;">' . round($sum_y) . '</td></tr><tr><td style="border: 1px solid black"></td><td style="border: 1px solid black"></td><td style="border: 1px solid black"></td></tr></table>';
                            }
                            if (!empty($employe)) {
                                $empolyee_name  = (isset($employe['emp_name'])) ?  $employe['emp_name'] : '';
                                $empolyee_image  = (isset($employe['avatar'])) ?  '<img src="' . htmlspecialchars($employe['avatar'], ENT_QUOTES, 'UTF-8') . '" alt="Employee Image" style="width: 100px; height: 120px;">' : '';    //edited by ANUKRISHNAN on 14-01-25
                                $employee_currentdate = date('d-m-Y');
                                $employee_id    = (isset($employe['employee_id'])) ?  $employe['employee_id'] : '';
                                $blood_group    = (isset($employe['blood'])) ?  $employe['blood'] : '';
                                $issued_date    = date('d-m-Y');
                                $emp_type    = (isset($employe['emp_type'])) ?  $employe['emp_type'] : '';
                                $designation    = (isset($employe['designation'])) ?  $employe['designation'] : '';
                                $department     = (isset($employe['department'])) ?  $employe['department'] : '';
                                $branch         = (isset($employe['branch'])) ?  $employe['branch'] : '';
                                $branch_address = (isset($employe['branch_address'])) ?  $employe['branch_address'] : '';
                                $joining_date   = (isset($employe['joining_date'])) ?  date("d-m-Y", strtotime($employe['joining_date'])) : '';    //edited by ASHIN on 01-11-24
                                $notice_days   = (isset($employe['notice_days'])) ?  $employe['notice_days'] : '';
                                $address   = (isset($employe['address'])) ?  $employe['address'] : '';
                                $city   = (isset($employe['city'])) ?  $employe['city'] : '';
                                $state   = (isset($employe['state'])) ?  $employe['state'] : '';
                                $pincode   = (isset($employe['pincode'])) ?  $employe['pincode'] : '';
                                $mobile_no   = (isset($employe['mobile_no'])) ?  $employe['mobile_no'] : '';
                                $email   = (isset($employe['email'])) ?  $employe['email'] : '';
                                $gender   = (isset($employe['gender'])) ?  $employe['gender'] : '';
                                $maritual_status   = (isset($employe['maritual_status'])) ?  $employe['maritual_status'] : '';
                                $guardian  = (isset($employe['guardian'])) ?  $employe['guardian'] : '';
                                $education   = (isset($employe['education'])) ?  $employe['education'] : '';
                                $dob   = (isset($employe['dob'])) ? date("d-m-Y", strtotime($employe['dob'])) : '';         //edited by ASHIN on 30-10-24
                                $bank_name   = (isset($employe['bank_name'])) ?  $employe['bank_name'] : '';
                                $branch_name   = (isset($employe['branch_name'])) ?  $employe['branch_name'] : '';
                                $bank_address   = (isset($employe['bank_address'])) ?  $employe['bank_address'] : '';
                                $ifsc_code   = (isset($employe['ifsc_code'])) ?  $employe['ifsc_code'] : '';
                                $account_no   = (isset($employe['account_no'])) ?  $employe['account_no'] : '';
                                $ctc            = (isset($emp_fkey)) ? $html : '';
                                $yearly_ctc     = (isset($emp_fkey)) ? $html : '';
                                //Edited by Akshay on 15-7-2024
                                $result1 = $this->Documents->query("select SUM(ROUND(ectc.structure_det_value)) as monthly_ctc from emp_salary_structure as ectc 
                                where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '$emp_fkey'   and ectc.end_date_effective is null 
                                and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4)) 
                                ");
                                // $result1 = $this->Documents->query("select SUM(ROUND(ectc.structure_det_value)) as monthly_ctc from emp_salary_structure as ectc 
                                // where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '$emp_fkey'   and ectc.end_date_effective is null 

                                // ");

                                $monthly_ctc_value = isset($result1[0][0]['monthly_ctc']) ? $result1[0][0]['monthly_ctc'] : '';
                                $monthly_ctc = (isset($emp_fkey)) ? $monthly_ctc_value : '';
                                $duration = (isset($employe['probation_days'])) ?  $employe['probation_days'] : '';
                                $salary_brkup = (isset($emp_fkey)) ? $html : '';
                                //End
                            }
                        }
                        break;
                    case 'comp_contact':
                        if ($company_fkey == 0) {
                            $company_name  = '{:comp_business_name}';
                            $company_nature  = '{:comp_business_nature}';
                            $company_type  = '{:comp_business_type}';
                            $company_address  = '{:comp_address}';
                            $company_city  = '{:comp_city';
                            $company_pincode  = '{:comp_pincode}';
                            $company_state  = '{:comp_state}';
                            $company_phone  = '{:comp_phone}';
                            $company_fax  = '{:comp_fax}';
                            $company_logo  = '{:comp_logo}';
                        } else {
                            $fields = 'business_name,business_nature,business_type,address,city,pincode,state,phone,fax,logo';
                            $companyDetails = $this->getCompanies($company_fkey, $fields);
                            $company = (isset($companyDetails) && isset($companyDetails[0])) ?  $companyDetails[0] : array();

                            if (!empty($company)) {
                                $company_name  = (isset($company['business_name'])) ?  $company['business_name'] : '';
                                $company_nature  = (isset($company['business_nature'])) ?  $company['business_nature'] : '';
                                $company_type  = (isset($company['business_type'])) ?  $company['business_type'] : '';
                                $company_address  = (isset($company['address'])) ?  $company['address'] : '';
                                $company_city  = (isset($company['city'])) ?  $company['city'] : '';
                                $company_pincode  = (isset($company['pincode'])) ?  $company['pincode'] : '';
                                $company_state  = (isset($company['state'])) ?  $company['state'] : '';
                                $company_phone  = (isset($company['phone'])) ?  $company['phone'] : '';
                                $company_fax  = (isset($company['fax'])) ?  $company['fax'] : '';
                                $company_logo  = (isset($company['logo'])) ?  '<img src="' . htmlspecialchars($company['logo'], ENT_QUOTES, 'UTF-8') . '" alt="Employee Image" style="width: 100px; height: 120px;">' : '';
                            }
                        }
                        break;
                    case 'branch':
                        if ($branch_fkey == 0) {
                            $branch_name  = '{:branch_name}';
                            $branch_address  = '{:branch_address}';
                            $branch_city  = '{:branch_city}';
                            $branch_state  = '{:branch_state}';
                            $branch_pincode  = '{:branch_pincode}';
                            $branch_email  = '{:branch_email}';
                        } else {
                            $fields = ' branch_name,address,city,state,pincode '; //,email'; // doubt in db there is no email field
                            $branchDetails = $this->getBraches($branch_fkey, $fields);
                            $branchData = (isset($branchDetails) && isset($branchDetails[0])) ?  $branchDetails[0] : array();
                            if (!empty($branchData)) {
                                $branch_name  = (isset($branchData['branch_name'])) ?  $branchData['branch_name'] : '';
                                $branch_address  = (isset($branchData['address'])) ?  $branchData['address'] : '';
                                $branch_city  = (isset($branchData['city'])) ?  $branchData['city'] : '';
                                $branch_state  = (isset($branchData['state'])) ?  $branchData['state'] : '';
                                $branch_pincode  = (isset($branchData['pincode'])) ?  $branchData['pincode'] : '';
                                $branch_email  = (isset($branchData['email'])) ?  $branchData['email'] : '';
                            }
                        }
                        break;
                    case 'supplier':
                        $fields = " concat(first_name,' ',ifnull(`middle_name`,''),' ',ifnull(last_name,'')) AS `name`,
                        company_name,email,phone,address,city,state,pincode ";
                        $supplierDetails = $this->getContacts('Vendor', $supplier_fkey, $fields);
                        $supplierData = (isset($supplierDetails) && isset($supplierDetails[0])) ?  $supplierDetails[0] : array();
                        if (!empty($supplierData)) {
                            $supplier_name  = (isset($supplierData['name'])) ?  $supplierData['name'] : '';
                            $supplier_comp_name  = (isset($supplierData['company_name'])) ?  $supplierData['company_name'] : '';
                            $supplier_email  = (isset($supplierData['email'])) ?  $supplierData['email'] : '';
                            $supplier_phone  = (isset($supplierData['phone'])) ?  $supplierData['phone'] : '';
                            $supplier_address  = (isset($supplierData['address'])) ?  $supplierData['address'] : '';
                            $supplier_city  = (isset($supplierData['city'])) ?  $supplierData['city'] : '';
                            $supplier_state  = (isset($supplierData['state'])) ?  $supplierData['state'] : '';
                            $supplier_pincode  = (isset($supplierData['pincode'])) ?  $supplierData['pincode'] : '';
                        }
                        break;
                    case 'customer':
                        $fields = " concat(first_name,' ',ifnull(`middle_name`,''),' ',ifnull(last_name,'')) AS `name`,
                    company_name,email,phone,address,city,state,pincode ";
                        $customerDetails = $this->getContacts('Customer', $customer_fkey, $fields);
                        $customerData = (isset($customerDetails) && isset($customerDetails[0])) ?  $customerDetails[0] : array();
                        if (!empty($customerData)) {
                            $customer_name  = (isset($customerData['name'])) ?  $customerData['name'] : '';
                            $customer_comp_name  = (isset($customerData['company_name'])) ?  $customerData['company_name'] : '';
                            $customer_email  = (isset($customerData['email'])) ?  $customerData['email'] : '';
                            $customer_phone  = (isset($customerData['phone'])) ?  $customerData['phone'] : '';
                            $customer_address  = (isset($customerData['address'])) ?  $customerData['address'] : '';
                            $customer_city  = (isset($customerData['city'])) ?  $customerData['city'] : '';
                            $customer_state  = (isset($customerData['state'])) ?  $customerData['state'] : '';
                            $customer_pincode  = (isset($customerData['pincode'])) ?  $customerData['pincode'] : '';
                        }
                        break;
                    case 'other':
                        $fields = " concat(first_name,' ',ifnull(`middle_name`,''),' ',ifnull(last_name,'')) AS `name`,
                    company_name,email,phone,address,city,state,pincode ";
                        $othersDetails = $this->getContacts('Others', $others_fkey, $fields);
                        $othersData = (isset($othersDetails) && isset($othersDetails[0])) ?  $othersDetails[0] : array();
                        if (!empty($othersData)) {
                            $others_name  = (isset($othersData['name'])) ?  $othersData['name'] : '';
                            $others_comp_name  = (isset($othersData['company_name'])) ?  $othersData['company_name'] : '';
                            $others_email  = (isset($othersData['email'])) ?  $othersData['email'] : '';
                            $others_phone  = (isset($othersData['phone'])) ?  $othersData['phone'] : '';
                            $others_address  = (isset($othersData['address'])) ?  $othersData['address'] : '';
                            $others_city  = (isset($othersData['city'])) ?  $othersData['city'] : '';
                            $others_state  = (isset($othersData['state'])) ?  $othersData['state'] : '';
                            $others_pincode  = (isset($othersData['pincode'])) ?  $othersData['pincode'] : '';
                        }
                        break;
                    case 'separation':
                        $fields = " submitted_date, last_applied_date, last_working_date, last_approved_working_date ";
                        $separationDetails = $this->getTerminations($emp_fkey, $fields);
                        $separationData = (isset($separationDetails) && isset($separationDetails[0])) ?  $separationDetails[0] : array();

                        $submitted_date = isset($separationData['submitted_date']) ? date('d-m-Y', strtotime($separationData['submitted_date'])) : '';
                        $last_applied_date = isset($separationData['last_applied_date']) ? date('d-m-Y', strtotime($separationData['last_applied_date'])) : '';
                        $last_working_date = isset($separationData['last_working_date']) ? date('d-m-Y', strtotime($separationData['last_working_date'])) : '';
                        $last_approved_working_date = isset($separationData['last_approved_working_date']) ? date('d-m-Y', strtotime($separationData['last_approved_working_date'])) : '';
                        date_default_timezone_set('Asia/Kolkata');
                        $current_date = date('d-m-Y');
                        //End
                        break;
                }
            }

            $placeholderArr = $this->getPlaceholder();
            $replace = array(
                $empolyee_name,
                $empolyee_image,   //edited by ANUKRISHNAN on 14-01-25
                $employee_currentdate,
                $employee_id,
                $blood_group,
                $issued_date,
                $emp_type,
                $designation,
                $department,
                $branch,
                $branchaddress,
                $joining_date,
                $notice_days,
                $address,
                $city,
                $state,
                $pincode,
                $mobile_no,
                $email,
                $gender,
                $maritual_status,
                $guardian,
                $education,
                $dob,
                $bank_name,
                $branch_name,
                $bank_address,
                $ifsc_code,
                $account_no,
                $ctc,
                $yearly_ctc,
                //Edited by Akshay on 15-7-2024
                $duration,
                $monthly_ctc,
                $salary_brkup,
                //End
                $company_name,
                $company_nature,
                $company_type,
                $company_address,
                $company_city,
                $company_pincode,
                $company_state,
                $company_phone,
                $company_fax,
                $company_logo,
                $branch_name,
                $branch_address,
                $branch_city,
                $branch_state,
                $branch_pincode,
                $branch_email,
                $supplier_name,
                $supplier_comp_name,
                $supplier_email,
                $supplier_phone,
                $supplier_address,
                $supplier_city,
                $supplier_state,
                $supplier_pincode,
                $customer_name,
                $customer_comp_name,
                $customer_email,
                $customer_phone,
                $customer_address,
                $customer_city,
                $customer_state,
                $customer_pincode,
                $others_name,
                $others_comp_name,
                $others_email,
                $others_phone,
                $others_address,
                $others_city,
                $others_state,
                $others_pincode,
                //Edited by Akshay on 8-5-2024
                $submitted_date,
                $last_applied_date,
                $last_working_date,
                $last_approved_working_date,
                $current_date

            );

            $html = str_replace($placeholderArr, $replace, $template);
        }
        if ($preview == 3) {
            echo json_encode(array('status' => true, 'html' => $html));
            exit;
        }
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->set_option('isRemoteEnabled', TRUE);
        /* Render the HTML as PDF */
        $dompdf->render();


        /* Output the generated PDF to Browser */
        if ($preview == 0) {
            $dompdf->stream($file_name, array("Attachment" => false));
        } else {
            $dompdf->stream($file_name);
        }
    }
    public function getPlaceholder()
    {

        return array(
            '{:empolyee_name}',
            //edited by athira on 15-01-2025
            '{:empolyee_image}',   //edited by ANUKRISHNAN on 14-01-25
            '{:employee_currentdate}',
            //end
            '{:employee_id}',
            '{:blood_group}',
            '{:issued_date}',
            '{:employee_emp_type}',
            '{:employee_designation}',
            '{:employee_department}',
            '{:employee_branch}',
            '{:employee_branch_address}',
            '{:employee_joining_date}',
            '{:employee_notice_days}',
            '{:employee_address}',
            '{:employee_city}',
            '{:employee_state}',
            '{:employee_pincode}',
            '{:employee_mobile_no}',
            '{:employee_email}',
            '{:employee_gender}',
            '{:employee_maritual_status}',
            '{:employee_guardian}',
            '{:employee_education}',
            '{:employee_date_of_birth}',
            '{:employee_bank_name}',
            '{:employee_branch_name}',
            '{:employee_bank_address}',
            '{:employee_ifsc_code}',
            '{:employee_account_no}',
            '{:employee_ctc}',
            '{:employee_yearly_ctc}',
            //Edited by Akshay on 15-7-2024
            '{:employee_duration}',
            '{:monthly_ctc}',
            '{:salary_break_up}',

            //End

            '{:comp_business_name}',
            '{:comp_business_nature}',
            '{:comp_business_type}',
            '{:comp_address}',
            '{:comp_city}',
            '{:comp_pincode}',
            '{:comp_state}',
            '{:comp_phone}',
            '{:comp_fax}',
            '{:comp_logo}',

            '{:branch_name}',
            '{:branch_address}',
            '{:branch_city}',
            '{:branch_state}',
            '{:branch_pincode}',
            '{:branch_email}',

            '{:supplier_name}',
            '{:supplier_comp_name}',
            '{:supplier_email}',
            '{:supplier_phone}',
            '{:supplier_address}',
            '{:supplier_city}',
            '{:supplier_state}',
            '{:supplier_pincode}',

            '{:customer_name}',
            '{:customer_comp_name}',
            '{:customer_email}',
            '{:customer_phone}',
            '{:customer_address}',
            '{:customer_city}',
            '{:customer_state}',
            '{:customer_pincode}',

            '{:others_name}',
            '{:others_comp_name}',
            '{:others_email}',
            '{:others_phone}',
            '{:others_address}',
            '{:others_city}',
            '{:others_state}',
            '{:others_pincode}',
            //Edited by Akshay on 8-5-2024
            '{:submitted_date}',
            '{:last_applied_date}',
            '{:last_working_date}',
            '{:last_approved_working_date}',
            '{:current_date}',

        );
    }
    public function saveDocument()
    {
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $data['emp_fkey'] = isset($_POST['employee']) ? $_POST['employee'] : 0;
        $data['template_fkey'] = isset($_POST['template']) ? $_POST['template'] : 0;
        $data['company_fkey'] = isset($_POST['company']) ? $_POST['company'] : 0;
        $data['branch_fkey'] = isset($_POST['branches']) ? $_POST['branches'] : 0;
        $data['supplier_fkey'] = isset($_POST['suppliers']) ? $_POST['suppliers'] : 0;
        $data['customer_fkey'] = isset($_POST['customers']) ? $_POST['customers'] : 0;
        $data['others_fkey'] = isset($_POST['others']) ? $_POST['others'] : 0;
        $tem = $this->Documents->query("select `template_name` from  `doc_template` where template_pkey = " . $data['template_fkey']);
        $template = $tem['0']['doc_template']['template_name'];
        $empname = '';
        if ($data['emp_fkey']) {
            $emp = $this->Documents->query("select `first_name` from  `emp_details` where emp_pkey = " . $data['emp_fkey']);
            $empname = $emp['0']['emp_details']['first_name'];
        }
        if ($empname == '') {
            $data['document_name'] = $template . '_Document';
        } else {
            $data['document_name'] = $template . '_' . $empname;
        }
        //$data['document_name'] = isset($_POST['document_name'])?$_POST['document_name']:'';
        $data['document'] = isset($_POST['document_content']) ? $_POST['document_content'] : '';
        $data['created_by']    = $loginUser;
        date_default_timezone_set('Asia/Kolkata');
        $data['creation_date'] = date('Y-m-d H:i:s');    //edited by ASHIN on 30-10-24
        $this->Documents->save($data);
        $pkey = $this->Documents->getLastInsertID();
        $data['doc_id'] = 'Doc_1000' . $pkey;
        $this->Documents->updateAll(array('Documents.doc_id' => "'" . $data['doc_id'] . "'"), array('Documents.document_pkey' => $pkey));
        echo json_encode(array('msg' => 'Document created successfully.'));
    }
    public function deleteDocument()
    {
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $data = array();
        $data['document_pkey']      = $_GET['ids'];
        $data['modified_by']        = $loginUser;
        $data['modification_date']  = date('Y-m-d H:i:s');       //edited by ASHIN on 30-10-24
        $data['status']             = 0;
        $this->Documents->save($data, true, array('modified_by', 'modification_date', 'status'));
        echo json_encode(array('msg' => 'Document deleted successfully.'));
    }
    public function getEmployees($emp_id = '')
    {
        $condition = ($emp_id != '') ? ' where emp_pkey = ' . $emp_id : '';

        $this->Documents->useDbConfig = $this->Session->read('ds');
        $result = $this->Documents->query("select `ed`.`emp_pkey` AS `emp_pkey`,`ed`.`guradian` as guardian,`ed`.`blood` as blood,
        concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,'')) AS `emp_name`,`ep`.`emp_type` as `emp_type`,
        `ep`.`emp_company_id` AS `employee_id`,`br`.`branch_name` AS `branch`,`br`.`address` AS `branch_address`,
		`ep`.`notice_days` as `notice_days`,`ed`.`address` as `address`,`ed`.`city` as `city`,`ed`.`state` as `state`,`ed`.`pincode` as `pincode`,`ed`.`mobile_no` as `mobile_no`,`ed`.`email` as `email`,`ed`.`classification` as `gender`,`ed`.`maritual_status` as `maritual_status`,`ed`.`education` as `education`,`ed`.`date_of_birth` as `dob`,`ed`.`bank_name` as `bank_name`,`ed`.`branch_name` as `branch_name`,`ed`.`branch_address` as `bank_address`,`ed`.`ifsc_code` as `ifsc_code`,`ed`.`account_no` as `account_no`,
		`designation`.`desig_name` AS `designation`,
        `department`.`dept_name` AS `department`,`ep`.`joining_date` AS `joining_date`, `ep`.`attr2` AS `probation_days`,
         `uc`.`avatar` AS `avatar`
         from ((((`emp_proff` `ep` join `emp_details` `ed` on((`ed`.`emp_pkey` = `ep`.`emp_fkey`) and `ed`.`status` = 1)) 
         left join `branches` `br` on((`ed`.`branch_code` = `br`.`branch_code`) and (`br`.`status` = 1))) 
         left join `designation` on(((`ep`.`designation` = `designation`.`desig_code`) 
         and (`designation`.`status` = 1)))) 
         left join `department` on(((`ep`.`emp_dept` = `department`.`dept_code`) and (`department`.`status` = 1)))
         left join `user_credentials` `uc` ON (`ep`.`emp_fkey` = `uc`.`emp_fkey`)
         ) " . $condition . " order by ed.first_name");
        // var_dump($result);
        $employees = [];
        foreach ($result as $key => $val) {
            $employees[] =  array_merge($val['ed'], $val[0], $val['ep'], $val['br'], $val['designation'], $val['department'], $val['uc']);
        }
        return $employees;
    }
    public function getCompanies($company_id = '', $fields = ' id,`business_name` ')
    {

        $condition = ($company_id != '') ? ' where id = ' . $company_id : '';
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $result = $this->Documents->query("SELECT $fields FROM `comp_contact_info`  " . $condition);
        $companiees = [];
        foreach ($result as $key => $val) {
            $companiees[] = $val['comp_contact_info'];
        }
        return $companiees;
    }
    public function getBraches($branch_id = '', $fields = " id, concat( branch_code,' - ',branch_name) as branch_name ")
    {

        $condition = ($branch_id != '') ? ' and id = ' . $branch_id : '';
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $result = $this->Documents->query("SELECT $fields FROM branches where status = 1 " . $condition);
        $branches = [];
        foreach ($result as $key => $val) {
            $branches[] =  array_merge($val['branches'], isset($val[0]) ? $val[0] : array());
        }
        return $branches;
    }
    public function getContacts($type, $id = '', $fields = " contact_id,concat(first_name,' ',ifnull(`middle_name`,''),' ',ifnull(last_name,'')) AS `name` ")
    {
        $condition = ($id != '') ? ' where contact_id = ' . $id : (isset($type) ? " where relationship='" . $type . "'" : "");
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $result = $this->Documents->query("select $fields FROM `contacts` " . $condition);
        $contacts = [];
        foreach ($result as $key => $val) {
            $contacts[] =  array_merge($val['contacts'], $val[0]);
        }
        return $contacts;
    }
    public function getTemplatePlaceholers($template_id)
    {

        $this->layout = '';
        $this->autoRender = false;
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $template = $this->DocTemplate->query('select  placeholders,template_content from doc_template where template_pkey = ' . $template_id . ' and status = 1 ');
        $plcholder = isset($template[0]['doc_template']['placeholders']) ? $template[0]['doc_template']['placeholders'] : '';
        $placeholders = '';
        if ($plcholder != '') {
            $placeholders = explode(",", $template[0]['doc_template']['placeholders']);
        } else {
            $placeholders = array('0' => 'emp');
        }
        $template_content = (isset($template[0]) && isset($template[0]['doc_template']['template_content'])) ?  $template[0]['doc_template']['template_content'] : '';
        $result  = [];
        foreach ($placeholders as $placeholder) {
            switch ($placeholder) {
                case 'emp':
                    // Edited by Akshay on 7-2-2025
                    $current_emp_pkey = $this->Session->read('emp_fkey');
                    $user_group = $this->Session->read('user_group');
                    $company_code = $this->Session->read('company_code');
                    $branch_condition = "";
                    if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $arr_is_ho = $this->EmployeeDetails->query(
                            "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                            ['emp_pkey' => $current_emp_pkey]
                        );
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $result[$placeholder] = $this->getEmployeesforABS($is_ho);
                        }else{
                            $result[$placeholder] = $this->getEmployees();
                        }
                    }else{
                        $result[$placeholder] = $this->getEmployees();
                    }
                    // End

                    break;
                case 'comp_contact':
                    $result[$placeholder] = $this->getCompanies();
                    break;
                case 'branch':
                    $result[$placeholder] = $this->getBraches();
                    break;
                case 'supplier':
                    $result[$placeholder] = $this->getContacts('Vendor');
                    break;
                case 'customer':
                    $result[$placeholder] = $this->getContacts('Customer');
                    break;
                case 'other':
                    $result[$placeholder] = $this->getContacts('Others');
                    break;
            }
        }
        //debug($placeholders);exit;
        echo json_encode(array('data' => $result, 'placeholer' => $placeholders, 'template' => $template_content));
    }
    public function savefile()
    {
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $user_id = $this->Session->read('login_user_id');
        $resp = array();
        $resp['error'] = '';
        $this->autoRender = FALSE;
        $companycode = strtolower($this->Session->read('company_code'));
        $target_dir = "./img/documents/" . $companycode . "/";
        try {

            if (
                !isset($_FILES['avatarfile']['error']) ||
                is_array($_FILES['avatarfile']['error'])
            ) {
                $resp['error'] = 'Invalid parameters.';
            }
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, TRUE);
            }
            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['avatarfile']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $resp['error'] = 'No file sent.';
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $resp['error'] = 'Exceeded filesize limit.';
                default:
                    $resp['error'] = 'Unknown errors.';
            }

            // You should also check filesize here. 
            if ($_FILES['avatarfile']['size'] > 1000000) {
                $resp['error'] = 'Exceeded filesize limit.';
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($_FILES['avatarfile']['tmp_name']),
                array(
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    //'pdf'=> 'application/pdf'
                    // 'xlxs' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    // 'docx' => 'application/zip'

                ),
                true
            )) {
                $resp['error'] = 'Invalid file format.';
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.


            $filename = sprintf($target_dir . '%s.%s', sha1_file($_FILES['avatarfile']['tmp_name']), $ext);
            $dir = sprintf('img/documents/' . $companycode . '/%s.%s', sha1_file($_FILES['avatarfile']['tmp_name']), $ext);
            if (!move_uploaded_file(
                $_FILES['avatarfile']['tmp_name'],
                $filename
            )) {
                $resp['error'] = 'Failed to move uploaded file.';
            }
//"https://mpm.office24.online/"
            $data['documents'] = $url =   $dir;// edited by bindu 09-09-25
            $resp['data'] = $data;
            $this->Documents->useDbConfig = $this->Session->read('ds');
            $result = $this->Documents->query("insert into doc_images(image_url,company_code,created_by) values('$url','$companycode','$user_id')");
        } catch (RuntimeException $e) {

            //  echo $e->getMessage();
        }
        echo json_encode($resp);
    }



    //Edited by Akshay on 27-10-2023
    public function documentMaster()
    {
        $user_group = $this->Session->read('user_group');
        $this->set("user_group", $user_group);
        $this->set('current_feature_id', $this->Session->read('current_feature_id'));
    }

    public function uploadForm()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        // $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        //Company ID Added by ***ARUL P DAS on 20/12/2019

        $user_group = $this->Session->read('user_group');

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

    //To allocate document to users
    public function documentAllocate($site_pkey = 0)
    {

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $condition = array("EmployeeDetails.emp_pkey not in (select emp_fkey from document_allocation where document_upload_fkey=$site_pkey and status=1)");
        //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
        $this->set("site_pkey", $site_pkey);
        //The below code is to display only unallocated employees to the specified store in employee list. By ***ARUL P DAS on 17/1/2020
        // $condition="`emp_pkey` not in (select emp_pkey from site where site_pkey=$site_pkey and site.status=1)";
        $arr_employees = $this->EmployeeDetails->find('all', [
            'fields' => ['ei.employee_id', 'EmployeeDetails.*'],
            'conditions' => ['EmployeeDetails.status' => 1, $condition],
            'joins' => [
                [
                    'table' => 'employee_info',
                    'alias' => 'ei',
                    'type' => 'INNER',
                    'conditions' => 'ei.emp_pkey = EmployeeDetails.emp_pkey'
                ]
            ],
            'order' => ['ei.EmpName']
        ]);

        $this->set("arr_employees", $arr_employees);
        //query edited by ***ARUL P DAS on 17/1/2020
        // $arr_employees_allocates = $this->EmployeeDetails->query("select distinct emp_fkey,emp_details.first_name,last_name from document_allocation join emp_details on (emp_details.emp_pkey = document_allocation.emp_fkey) where document_upload_fkey = '$site_pkey' and document_allocation.status = '1'");
        $arr_employees_allocates = $this->EmployeeDetails->query("
                                                                            SELECT 
                                                                                DISTINCT document_allocation.emp_fkey,
                                                                                employee_info.employee_id,
                                                                                emp_details.first_name,
                                                                                emp_details.last_name 
                                                                            FROM 
                                                                                document_allocation 
                                                                            JOIN 
                                                                                emp_details ON (emp_details.emp_pkey = document_allocation.emp_fkey) 
                                                                            JOIN 
                                                                                employee_info ON (employee_info.emp_pkey = document_allocation.emp_fkey) 
                                                                            WHERE 
                                                                                document_allocation.document_upload_fkey = '$site_pkey' 
                                                                                AND document_allocation.status = '1'
                                                                            ORDER BY 
                                                                                employee_info.EmpName
                                                                        ");


        // 
        //        $arr_employees_allocates = $this->EmployeeDetails->query(" select emp_pkey,emp_details.first_name,last_name from access_store join emp_details on (emp_details.emp_pkey = access_store.emp_fkey) where store_pkey = '$store_pkey' and access_store.status = '1' and emp_pkey != access_store.emp_fkey ");
        $this->set("arr_employees_allocates", $arr_employees_allocates);
        //            debug($arr_employees_allocates);
    }


    public function save_allocate()
    {
        $this->autoRender = FALSE;
        // $this->Store->useDbConfig = $this->Session->read('ds');
        // $this->site_access->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $loginUser = $this->Session->read('login_user_id');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $emps = $arr_form_data['emps'];
        $site_code = $arr_form_data['site_code'];
        $resp_att = array();
        try {
            if ($emps != 'all-employees') {
                $cnt = $this->EmployeeDetails->query("select count(*) as count from document_allocation where document_upload_fkey='$site_code' and emp_fkey='$emps' and status=1 ");
                $count = $cnt['0']['0']['count'];

                if ($count == 0) {
                    try {
                        date_default_timezone_set('Asia/Kolkata');
                        $currentTime = date('Y-m-d H:i:s');    //edited by ASHIN on 30-10-24
                        $this->EmployeeDetails->query(" insert into document_allocation(document_upload_fkey,emp_fkey,allocated_date,allocated_by) values('$site_code','$emps','$currentTime','$loginUser') ");
                        $this->EmployeeDetails->query("UPDATE document_upload
                                                                    SET document_allocated_date = '$currentTime', document_allocated_by = '$loginUser'
                                                                    WHERE document_upload_pkey = $site_code AND status = 1;
                                                                    ");
                        $resp_att['success'] = 1;
                        $resp_att['msg'] = "Employee document allocation successful.";
                        echo json_encode($resp_att);
                    } catch (Exception $e) {
                        $resp_att['success'] = 0;
                        $resp_att['msg'] = "Saving failed.";
                        echo json_encode($resp_att);
                    }
                } else {
                    $resp_att['success'] = 0;
                    $resp_att['msg'] = "Employee already exists.";
                    echo json_encode($resp_att);
                }
            } else {
                $arr_emp_fkey = $this->EmployeeDetails->query("SELECT DISTINCT emp_pkey FROM employee_info ei
                        WHERE emp_status = 1
                        AND emp_pkey NOT IN (SELECT emp_fkey FROM document_allocation 
                                                WHERE document_upload_fkey = $site_code
                                                AND end_date_effective IS NULL
                                                AND status = 1 )");
                // debug( $arr_emp_fkey); exit;

                $responses = array(); // Initialize an array to store multiple responses
                if (count($arr_emp_fkey) > 0) {
                    foreach ($arr_emp_fkey as $val) {
                        $emp_pkey = isset($val["ei"]["emp_pkey"]) ? $val["ei"]["emp_pkey"] : 0;

                        $cnt = $this->EmployeeDetails->query("select count(*) as count from document_allocation where document_upload_fkey='$site_code' and emp_fkey='$emp_pkey' and status=1 ");
                        $count = $cnt['0']['0']['count'];

                        $response = array(); // Store each response in a separate array

                        if ($count == 0) {
                            try {
                                date_default_timezone_set('Asia/Kolkata');
                                $currentTime = date('Y-m-d H:i:s');     //edited by ASHIN on 30-10-24
                                $this->EmployeeDetails->query(" insert into document_allocation(document_upload_fkey,emp_fkey,allocated_date,allocated_by) values('$site_code','$emp_pkey','$currentTime','$loginUser') ");

                                $this->EmployeeDetails->query("UPDATE document_upload
                        SET document_allocated_date = '$currentTime', document_allocated_by = '$loginUser'
                        WHERE document_upload_pkey = $site_code AND status = 1;
                        ");



                                $response['success'] = 1;
                                $response['msg'] = "Employee document allocation successful.";
                            } catch (Exception $e) {
                                $response['success'] = 0;
                                $response['msg'] = "Saving failed.";
                            }
                        } else {
                            $response['success'] = 0;
                            $response['msg'] = "Employee already exists.";
                        }

                        // Push each response into the responses array
                        $response;
                    }
                    $arr_employees = $this->EmployeeDetails->query("SELECT DISTINCT ei.emp_pkey, ei.EmpName, ei.employee_id FROM employee_info ei 
                                                    WHERE ei.emp_pkey IN (SELECT DISTINCT da.emp_fkey FROM document_allocation da WHERE status = 1 AND da.end_date_effective IS NULL AND da.document_upload_fkey =$site_code)
                                                    AND ei.emp_status = 1
                                                    ORDER BY ei.EmpName 
                                                    ");
                    $response['data'] = $arr_employees;
                } else {
                    $response['success'] = 0;
                    $response['msg'] = "Employees already exists.";
                }


                // debug($arr_employees); exit;
                // Finally, echo the JSON representation of the array of responses
                echo json_encode($response);
            }
        } catch (Exception $e) {
        }
    }

    public function documentUpload()
    {
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');
        $userGroup = $this->Session->read('user_group');
        try {
            $this->autoRender = false;
            $this->layout = null;

            $dirsep = "/";
            $companyCode = strtoupper($this->Session->read('company_code'));
            date_default_timezone_set('Asia/Kolkata');
            $currentDateTime = date('Ymd_His');
            $fileWebrootPath = "document" . $dirsep . "file" . $dirsep . $companyCode . $dirsep;

            if (!file_exists(getcwd() . $dirsep . $fileWebrootPath)) {
                mkdir(getcwd() . $dirsep . $fileWebrootPath, 0755, true);
            }

            if (isset($_FILES['pdfFile'])) {
                $uploadedFile = $_FILES['pdfFile'];
                $created_by = $this->Session->read('login_user_id');
                date_default_timezone_set('Asia/Kolkata');

                if ($uploadedFile['error'] === UPLOAD_ERR_OK) {

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $fileType = finfo_file($finfo, $uploadedFile['tmp_name']);
                    finfo_close($finfo);

                    $filename = $companyCode . '_' . $currentDateTime . '_' . uniqid() . '_' . $uploadedFile['name'];
                    $filename = preg_replace('/[^\w.-]/', '_', $filename);
                    $targetPath = getcwd() . $dirsep . $fileWebrootPath . $filename;

                    if (move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
                        $message = 'Document successfully uploaded';
                        $currentTime = date('Y-m-d H:i:s');

                        $data = [
                            'document_name' => $uploadedFile['name'],
                            'document_path' => $targetPath,
                            'type' => $fileType,
                            'created_by' => $created_by,
                            'creation_date' => $currentTime,
                            'status' => 1 // Assuming status is set to 1
                        ];

                        $this->DocumentUpload->save($data);
                        return json_encode(['status' => 1, 'message' => $message, 'filename' => $filename]);
                    } else {
                        $message = 'Failed to move uploaded file.';
                        return json_encode(['success' => false, 'message' => $message]);
                    }
                } else {
                    $message = 'File upload failed.';
                    return json_encode(['success' => false, 'message' => $message]);
                }
            } else {
                $message = 'No file uploaded.';
                return json_encode(['success' => false, 'message' => $message]);
            }
        } catch (Exception $e) {
            // Handle exceptions here
        }
    }


    //Data for table
     public function getDocumentsFromDatabase()
    {
        $this->autoRender = false;
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');
        $livesite = $this->webroot;
        $this->set('livesite', $livesite);
        $userGroup = $this->Session->read('user_group');
        $loginUser = $this->Session->read('login_user_id');

        $arr_data = $this->request->data;

        $page = isset($arr_data['page']) ? $arr_data['page'] : 1;
        $rows = isset($arr_data['rows']) ? $arr_data['rows'] : 10;

        $temp = isset($arr_data['document_name']) ? $arr_data['document_name'] : '';
        if (!empty($temp)) {
            $conditions[] = ['document_name LIKE' => '%' . $temp . '%'];
        }

        $current_feature_id = $this->Session->read('current_feature_id');

        if ($userGroup == 1 || !empty($current_feature_id)) {
            $conditions['status'] = 1;
            $data = $this->DocumentUpload->find('all', [
                'conditions' => $conditions,
                'fields' => [
                    'document_upload_pkey',
                    'document_name',
                    'created_by',
                    'creation_date',
                    'document_allocated_by',
                    'document_allocated_date',
                    'document_path',
                    'type'
                ]
            ]);
        }else {
            $temp = isset($arr_data['emp']) ? $arr_data['emp'] : '';
            $employee = $this->DocumentUpload->query("SELECT emp_fkey FROM user_credentials WHERE user_id = '$loginUser'");
            // debug($employee );
            $emp_pkey = isset($employee[0]['user_credentials']['emp_fkey']) ? $employee[0]['user_credentials']['emp_fkey'] : '0';
            $conditions['status'] = 1;

            if (empty($temp)) {
                $data = $this->DocumentUpload->find('all', [
                    'conditions' => [
                        'status' => 1,
                        "document_upload_pkey IN (SELECT DISTINCT document_upload_fkey FROM document_allocation WHERE emp_fkey = $emp_pkey AND status = 1)"
                    ],
                    'fields' => [
                        'document_upload_pkey',
                        'document_name',
                        'created_by',
                        'creation_date',
                        'document_allocated_by',
                        'document_allocated_date',
                        'document_path',
                        'type'
                    ],

                ]);
            } else {
                $data = $this->DocumentUpload->find('all', [
                    'conditions' => [
                        'status' => 1,
                        "document_upload_pkey IN (SELECT DISTINCT document_upload_fkey FROM document_allocation WHERE emp_fkey = $emp_pkey AND status = 1)",
                        ['document_name LIKE' => '%' . $temp . '%'] // New condition for document_name
                    ],
                    'fields' => [
                        'document_upload_pkey',
                        'document_name',
                        'created_by',
                        'creation_date',
                        'document_allocated_by',
                        'document_allocated_date',
                        'document_path',
                        'type'
                    ],
                ]);
            }
        }



        $formattedData = [];
        foreach ($data as $row) {
            $pdfPath = $row['DocumentUpload']['document_upload_pkey'];
            $type = $row['DocumentUpload']['type'];
            $fileName = $row['DocumentUpload']['document_name'];
            $formattedData[] = [
                'document_pkey' => $row['DocumentUpload']['document_upload_pkey'],
                'document_name' => $row['DocumentUpload']['document_name'],
                'created_by' => $row['DocumentUpload']['created_by'],
                'creation_date' => $row['DocumentUpload']['creation_date'],
                'document_allocated_by' => $row['DocumentUpload']['document_allocated_by'],
                'document_allocated_date' => $row['DocumentUpload']['document_allocated_date'],
                'download_link' => "<a href='javascript:void(0)' onclick=\"loadPDFPreview('{$pdfPath}','{$type}','{$fileName}')\"><button>View</button></a>"
            ];
        }

        $start = ($page - 1) * $rows;
        $slicedData = array_slice($formattedData, $start, $rows);

        $response = [
            'total' => count($formattedData),
            'rows' => $slicedData
        ];

        // Respond with JSON data
        echo json_encode($response);
    }

    // Inside the controller function
    // Inside the controller function
    public function documentPreview($fileKey = 0)
    {
        $this->autoRender = false;
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');

        $fileKey = isset($_POST['pdfPath']) ? $_POST['pdfPath'] : 0;

        $file_path_array = $this->DocumentUpload->query("SELECT DISTINCT document_path FROM document_upload WHERE document_upload_pkey = $fileKey");

        $filePath = isset($file_path_array[0]['document_upload']['document_path']) ? $file_path_array[0]['document_upload']['document_path'] : '';

        if ($filePath) {
            $fileContent = file_get_contents($filePath);

            if ($fileContent !== false) {
                // Encode the file content to Base64
                $base64Content = base64_encode($fileContent);
                $response = [
                    'status' => 'success',
                    'fileData' => $base64Content,
                ];
                echo json_encode($response);
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to read the file content.',
                ];
                echo json_encode($response);
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'File path is missing.',
            ];
            echo json_encode($response);
        }
    }

    public function openFileInNewTab()
    {
        // This action should handle the opening of files in a new tab

        if ($this->request->is('post')) {
            $fileKey = $this->request->data['fileKey'];
            $fileType = $this->request->data['fileType'];

            // Process the file opening based on the type and key received
            if ($fileType !== 'application/pdf') {
                // Redirect to the file using its URL
                return $this->redirect($fileKey);
            }
            // Other file types or handling could be added based on your application requirements
        }

        // In case of incorrect usage or errors, handle accordingly
        throw new NotFoundException();
    }





    public function deleteDocumentFromGrid()
    {
        $this->layout = '';
        $this->autoRender = false;
        date_default_timezone_set('Asia/Kolkata');
        $loginUser = $this->Session->read('login_user_id');
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');

        $document_pkey = isset($_GET['ids']) ? $_GET['ids'] : 0;

        $allocation_count = $this->DocumentUpload->query("SELECT COUNT(DISTINCT emp_fkey) AS count_of_employees
                                                                FROM document_allocation
                                                                WHERE document_upload_fkey = $document_pkey
                                                                AND status = 1
                                                                AND end_date_effective IS NULL;
                                                                ");
        // debug($allocation_count); exit;
        $count = isset($allocation_count[0][0]['count_of_employees']) ? $allocation_count[0][0]['count_of_employees'] : 0;
        $data = array();
        if ($count == 0) {
            $date = date('Y-m-d H:i:s');    //edited by ASHIN on 30-10-24
            $status = 0;
            $this->DocumentUpload->query("UPDATE document_upload
                                            SET modified_by = ' $loginUser',
                                                modification_date = '$date', 
                                                status = 0
                                            WHERE document_upload_pkey = $document_pkey;
                                            ");
            echo json_encode(array('status' => 'success', 'msg' => 'Document deleted successfully.'));
        } else {
            echo json_encode(array('status' => 'failure', 'msg' => 'Document already allocated.'));
        }
    }


    //To remove employees from allocated list
    public function remove_allocate()
    {
        $this->autoRender = FALSE;
        // $this->Store->useDbConfig = $this->Session->read('ds');
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        // debug($arr_form_data); exit;
        $emps = $arr_form_data['emps'];
        $site_code = $arr_form_data['site_code'];

        $resp_att = array();
        try {


            $this->DocumentUpload->query("update document_allocation set status = '0' where document_upload_fkey = '$site_code' and emp_fkey = '$emps' ");
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Removed Successfully ";
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }

    public function pdfWindow() {}

    public function viewDocument($documentKey = 0)
    {
        $this->autoRender = false;
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');
        // debug($_POST); exit;
        $pdf_path_array = $this->DocumentUpload->query("SELECT DISTINCT document_path FROM document_upload 
                                                            WHERE document_upload_pkey = $documentKey");
        $pdfPath = isset($pdf_path_array[0]['document_upload']['document_path']) ? $pdf_path_array[0]['document_upload']['document_path'] : '';

        $this->set('pdfPath', $pdfPath);
    }


    public function fetch_employee_details()
    {
        try {
            $this->autoRender = false;
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $emp_pkey = isset($_POST['empId']) ? $_POST['empId'] : 0;
            $arr_employees = $this->EmployeeDetails->find('all', [
                'fields' => ['ei.employee_id', 'ei.EmpName', 'EmployeeDetails.emp_pkey'],
                'conditions' => ['EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_pkey],
                'joins' => [
                    [
                        'table' => 'employee_info',
                        'alias' => 'ei',
                        'type' => 'INNER',
                        'conditions' => 'ei.emp_pkey = EmployeeDetails.emp_pkey'
                    ]
                ]
            ]);
            $employee = isset($arr_employees[0]) ? $arr_employees[0] : '';
            echo json_encode($employee);
        } catch (Exception $e) {
        }
    }

    public function listitems()
    {
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');
        $template = $this->DocumentUpload->query("SELECT * FROM templates WHERE status = 1 ");
        $this->set('arr_template', $template);
    }

    public function template($id = '')
    {
        $id = $id;
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');
        $template = $this->DocumentUpload->query("SELECT * FROM templates WHERE id = $id ");
        $this->set('data', $template);
        $this->set('id', $id);

        $companycode = strtolower($this->Session->read('company_code'));
        $this->set('companycode', $companycode);

        $template_details = $this->DocumentUpload->query("SELECT * FROM templates_details WHERE templateid = $id ORDER BY id DESC LIMIT 1 ");
        $this->set('template', $template_details);
    }

    public function save_template()
    {
        $arr_data = $this->request->data;
        $this->autoRender = false;
        $this->layout = null;
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');

        $templid = isset($arr_data['templid']) ? $arr_data['templid'] : '';
        $text = isset($arr_data[0]) ? $arr_data[0] : '';
        $left = isset($arr_data['left']) ? $arr_data['left'] : '';
        $top = isset($arr_data['top']) ? $arr_data['top'] : '';
        $imageLeft = isset($arr_data['imageLeft']) ? $arr_data['imageLeft'] : '';
        $imageTop = isset($arr_data['imageTop']) ? $arr_data['imageTop'] : '';
        $imageWidth = isset($arr_data['imageWidth']) ? $arr_data['imageWidth'] : '';
        $imageHeight = isset($arr_data['imageHeight']) ? $arr_data['imageHeight'] : '';

        $insertData = $this->DocumentUpload->query("insert into templates_details(templateid, text_content, left_axis, top_axis) VALUES ($templid, '$text', '$left', '$top')");
        $insertData = $this->DocumentUpload->query("UPDATE templates SET imageLeft = $imageLeft, imageTop = $imageTop, imagesize = $imageWidth, imageHeight = $imageHeight WHERE id = $templid");
        echo json_encode(array('status' => 'success', 'msg' => 'Template Updated successfully.'));
    }

    function savedefault_template()
    {
        $arr_data = $this->request->data;
        $this->autoRender = false;
        $this->layout = null;
        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');

        $id = isset($arr_data['id']) ? $arr_data['id'] : '';
        $type = isset($arr_data['type']) ? $arr_data['type'] : '';

        $updateData = $this->DocumentUpload->query("UPDATE templates SET is_default = 0 WHERE status = 1 and type = '$type'");
        $updateData = $this->DocumentUpload->query("UPDATE templates SET is_default = 1 WHERE id = $id");
        echo json_encode(array('status' => 'success', 'msg' => 'Template Updated successfully.'));
    }

    public function sendemailtemplate($emp_fkey, $type = 'birthday')
    {
        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $this->DocumentUpload->useDbConfig = $this->Session->read('ds');
        $emp_det = $this->DocumentUpload->query("SELECT * FROM templates WHERE type = '$type' and is_default = 1 ");
        $emp_details = $this->DocumentUpload->query("SELECT * FROM user_credentials WHERE emp_fkey = $emp_fkey ");

        try {
            $email = $emp_details[0]['user_credentials']['email'];

            $image = $emp_details[0]['user_credentials']['avatar'] ? 'https://v1.mypayrollmaster.online/' .  $emp_details[0]['user_credentials']['avatar'] : 'https://t3.ftcdn.net/jpg/02/43/12/34/360_F_243123463_zTooub557xEWABDLk0jJklDyLSGl2jrr.jpg';

            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = true;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';                 // SMTP username
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to   

            $mail->setFrom('mypayrollmaster@office24.online');
            $mail->addAddress($email);     // Add a recipient
            //$mail->addCC('projects@greatleap.tech');     // Add a recipient
            $mail->addReplyTo('mypayrollmaster@office24.online');
            $mail->isHTML(true);                                  // Set email format to HTML
            // $mail->AddEmbeddedImage('https://login.mypayrollmaster.online/newlogin/img/logo.png', 'MPM');
            $mail->AltBody    = '<!DOCTYPE html>';


            $mail->Subject = 'MPM - Birthday Wish';

            $companycode = ($this->Session->read('company_code'));
            $mail->MsgHTML($this->renderImageTempalte($emp_det[0]['templates']['id'], $companycode, $image, $emp_details));



            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                echo 'Message has been sent';
            }
        } catch (Exception $ex) {
            var_dump('$ex->getMessage()');
        }
    }

    public function convertimage()
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $google_fonts = "Roboto";

        $data = array(
            'html' => '',
            'css' => '',
            'google_fonts' => $google_fonts,
            'url' => 'https://v1.mypayrollmaster.online/DocumentManager/renderImageTempalte'
        );

        // $ch = curl_init();

        // curl_setopt($ch, CURLOPT_URL, "https://hcti.io/v1/image");
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // curl_setopt($ch, CURLOPT_POST, 1);
        // // Retrieve your user_id and api_key from https://htmlcsstoimage.com/dashboard
        // curl_setopt($ch, CURLOPT_USERPWD, "3ffab2bd-74e7-4198-8153-263ff1c985d2" . ":" . "8ee1bf09-7791-48d6-96ae-e6ace86eafc4");

        // $headers = array();
        // $headers = "Content-Type: application/x-www-form-urlencoded";
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // $result = curl_exec($ch);
        // if (curl_errno($ch)) {
        //     echo 'Error:' . curl_error($ch);
        // }
        // curl_close($ch);
        // $res = json_decode($result, true);
        // var_dump($res);
        $res['url'] = '';
        $this->sendemailtemplate($res['url']);
        // https://hcti.io/v1/image/202dc04d-5efc-482e-8f92-bb51612c84cf
    }

    public function renderImageTempalte($id = '', $companyCode = '', $image = '', $emp_details = array())
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://v1.mypayrollmaster.online/api/v2qa/templateRender',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('user_id' => $companyCode . '12611', 'valueTodat' => '', 'roaster_master_pkey' => '45', 'id' => $id),
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=kmep5t5oca2oo1fa6dfe0l95t3'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $template = json_decode($response);

        $data = json_decode(file_get_contents('php://input'), true);
        $imagesize = isset($template->{0}->templates->imagesize) ? $template->{0}->templates->imagesize : '';
        $top_axis = isset($template->{0}->templates_details->top_axis) ? $template->{0}->templates_details->top_axis : '';
        $left_axis = isset($template->{0}->templates_details->left_axis) ? $template->{0}->templates_details->left_axis : '';
        $text_content = isset($template->{0}->templates_details->text_content) ? $template->{0}->templates_details->text_content : '';
        $imageHeight = isset($template->{0}->templates->imageHeight) ? $template->{0}->templates->imageHeight : '';
        $imageTop = isset($template->{0}->templates->imageTop) ? $template->{0}->templates->imageTop : '';
        $imageLeft = isset($template->{0}->templates->imageLeft) ? $template->{0}->templates->imageLeft : '';
        $imageg = isset($template->{0}->templates->image) ? $template->{0}->templates->image : '';

        $first_name = isset($emp_details[0]['user_credentials']['first_name']) ? $emp_details[0]['user_credentials']['first_name'] : '';
        $last_name = isset($emp_details[0]['user_credentials']['last_name']) ? $emp_details[0]['user_credentials']['last_name'] : '';

        $text_content = str_replace('{{first_name}}', $first_name, $text_content);
        $text_content = str_replace('{{last_name}}', $last_name, $text_content);

        // $imagesize = 58;
        // $top_axis = 161.4;
        // $left_axis = 809;
        // $text_content = '<u><i><b><font color="#000000" style="background-color: rgb(255, 255, 0);">Sanjun Dev</font></b></i></u>';
        // $imageHeight = 58;
        // $imageTop = 453.2;
        // $imageLeft = 884;

        $html = '
        <div style="
        position:relative;
        max-height:0;
        opacity:0.999;">
                <div class="" style="border: 1px solid #ccc; width: ' . $imagesize . 'px; height: ' . $imageHeight . 'px; display:inline-block; margin-top: ' . $imageTop . 'px; margin-left: ' . $imageLeft . 'px; ">
                    <img src="' . $image . '" style="width: inherit; height: inherit; object-fit: cover; ">
                </div>

        </div>

        <div style="
        position:relative;
        max-height:0;
        opacity:0.999;">
               

                <div class="" style="display:inline-block; margin-top: ' . ($top_axis + 60) . 'px; margin-left: ' . $left_axis . 'px; " class="">
                    <p class="" name="editordata">' . $text_content . '</p>
                </div>
        </div>
                <img src="https://v1.mypayrollmaster.online/' . ($imageg) . '" width="899" height="880" alt="" class="image" style="object-fit: contain; height: 880px; width:899px; ">';

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        return $html;
    }

    public function renderImageTempaltePrew($id = '', $companyCode = '')
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://v1.mypayrollmaster.online/api/v2qa/templateRender',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('user_id' => $companyCode . '12611', 'valueTodat' => '', 'roaster_master_pkey' => '45', 'id' => $id),
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=kmep5t5oca2oo1fa6dfe0l95t3'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $template = json_decode($response);

        $data = json_decode(file_get_contents('php://input'), true);
        $imagesize = isset($template->{0}->templates->imagesize) ? $template->{0}->templates->imagesize : '';
        $top_axis = isset($template->{0}->templates_details->top_axis) ? $template->{0}->templates_details->top_axis : '';
        $left_axis = isset($template->{0}->templates_details->left_axis) ? $template->{0}->templates_details->left_axis : '';
        $text_content = isset($template->{0}->templates_details->text_content) ? $template->{0}->templates_details->text_content : '';
        $imageHeight = isset($template->{0}->templates->imageHeight) ? $template->{0}->templates->imageHeight : '';
        $imageTop = isset($template->{0}->templates->imageTop) ? $template->{0}->templates->imageTop : '';
        $imageLeft = isset($template->{0}->templates->imageLeft) ? $template->{0}->templates->imageLeft : '';
        $imageg = isset($template->{0}->templates->image) ? $template->{0}->templates->image : '';

        // $imagesize = 58;
        // $top_axis = 161.4;
        // $left_axis = 809;
        // $text_content = '<u><i><b><font color="#000000" style="background-color: rgb(255, 255, 0);">Sanjun Dev</font></b></i></u>';
        // $imageHeight = 58;
        // $imageTop = 453.2;
        // $imageLeft = 884;

        $html = '
        <div style="
        position:relative;
        max-height:0;
        opacity:0.999;">
                <div class="" style="border: 1px solid #ccc; width: ' . $imagesize . 'px; height: ' . $imageHeight . 'px; display:inline-block; margin-top: ' . $imageTop . 'px; margin-left: ' . $imageLeft . 'px; ">
                    <img src="https://images.unsplash.com/photo-1633332755192-727a05c4013d?q=80&w=1000&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8dXNlcnxlbnwwfHwwfHx8MA%3D%3D" style="width: inherit; height: inherit; object-fit: cover; ">
                </div>

        </div>

        <div style="
        position:relative;
        max-height:0;
        opacity:0.999;">
               

                <div class="" style="display:inline-block; margin-top: ' . ($top_axis + 60) . 'px; margin-left: ' . $left_axis . 'px; " class="">
                    <p class="" name="editordata">' . $text_content . '</p>
                </div>
        </div>
                <img src="https://v1.mypayrollmaster.online/' . ($imageg) . '" width="899" height="880" alt="" class="image" style="object-fit: contain; height: 880px; width:899px; ">';

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        echo $html;
    }

    public function saveImage($templateID)
    {
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $user_id = $this->Session->read('login_user_id');
        $resp = array();
        $resp['error'] = '';
        $this->autoRender = FALSE;
        $companycode = strtolower($this->Session->read('company_code'));
        $target_dir = "./img/templatebg/" . $companycode . "/";
        try {

            if (
                !isset($_FILES['filename']['error']) ||
                is_array($_FILES['filename']['error'])
            ) {
                $resp['error'] = 'Invalid parameters.';
            }
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, TRUE);
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($_FILES['filename']['tmp_name']),
                array(
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    'jpeg' => 'image/png',

                ),
                true
            )) {
                $resp['error'] = 'Invalid file format.';
            }

            $filename = sprintf($target_dir . '%s.%s', sha1_file($_FILES['filename']['tmp_name']), $ext);
            $dir = sprintf('img/templatebg/' . $companycode . '/%s.%s', sha1_file($_FILES['filename']['tmp_name']), $ext);
            if (!move_uploaded_file(
                $_FILES['filename']['tmp_name'],
                $filename
            )) {
                $resp['error'] = 'Failed to move uploaded file.';
            }

            $data['documents'] = $url =  $dir;
            $resp['data'] = $data;
            $this->Documents->useDbConfig = $this->Session->read('ds');
            $result = $this->Documents->query("UPDATE templates SET image = '$url' WHERE id = $templateID ");
        } catch (RuntimeException $e) {

            //  echo $e->getMessage();
        }
        echo json_encode($resp);
    }

    public function templateform($template_pkey = '')
    {
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        if ($template_pkey != '') {
            $result = $this->DocTemplate->query('select * FROM templates WHERE id=' . $template_pkey . ' and status = 1 ');

            if (isset($result[0]) && isset($result[0]['templates'])) {
                $this->set('template_name', (isset($result[0]['templates']['name']) ? $result[0]['templates']['name'] : ""));
                $this->set('template_pkey', (isset($result[0]['templates']['id']) ? $result[0]['templates']['id'] : ""));
                $this->set('policy', (isset($result[0]['templates']['type']) ? $result[0]['templates']['type'] : ""));
            }
        }
    }

    public function saveBirthdayTemplate()
    {

        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->Templates->useDbConfig = $this->Session->read('ds');
        $this->TemplatesDetails->useDbConfig = $this->Session->read('ds');
        $data = $_POST;
        $this->Templates->save($data);
        $insertid = $this->Templates->getLastInsertID();
        $this->TemplatesDetails->save(array(
            'templateid' => $insertid,
            'left_axis' => 110,
            'top_axis' => 110,
            'text_content' => '',
        ));

        echo json_encode(array('msg' => 'Templates saved successfully.'));
    }

    //Edited by Akshay on 8-5-2024
    public function getTerminations($emp_fkey = 0, $fields = " submitted_date, last_applied_date, last_working_date, last_approved_working_date ")
    {

        $condition = ($emp_fkey != 0) ? ' and emp_fkey = ' . $emp_fkey : '';
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $result = $this->Documents->query("SELECT $fields FROM termination where status = 1 " . $condition);
        $resigned = [];
        foreach ($result as $key => $val) {
            $resigned[] =  array_merge($val['termination'], isset($val[0]) ? $val[0] : array());
        }
        return $resigned;
    }
    //End

    // Edited by Akshay on 13-1-2025
    public function printPreview($document_id = '')
    {
        $this->layout = '';
        $this->autoRender = false;
        $this->Documents->useDbConfig = $this->Session->read('ds');
        $file_name = 'Document.pdf';

        $html = ''; // Default empty HTML content

        if (!empty($document_id)) {
            $data = $this->Documents->query("SELECT document_name, document, creation_date, created_by, doc_id FROM documents WHERE document_pkey = " . intval($document_id));
            if (!empty($data)) {
                $date = isset($data[0]['documents']['creation_date']) ? $data[0]['documents']['creation_date'] : '';
                $created = isset($data[0]['documents']['created_by']) ? $data[0]['documents']['created_by'] : '';
                $html = isset($data[0]['documents']['document']) ? $data[0]['documents']['document'] : '';
                $doc = isset($data[0]['documents']['doc_id']) ? $data[0]['documents']['doc_id'] : '';
                $html .= '<style>
                #footer {
                    position: fixed;
                    left: 0;
                    bottom: 0;
                    right: 0;
                    height: 20px;
                    text-align: center;
                    padding: 10px;
                    font-size: 12px;
                    background: #f9f9f9;
                }
                </style>
                <div id="footer"> Generated by ' . $created . ' at ' . $date . ' with Document ID ' . $doc . '</div>';
            }
        }

        // Render HTML for print preview
        $print_html = '<html>
        <head>
            <title>Print Preview</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                @media print {
                    #print-button { display: none; }
                }
            </style>
        </head>
        <body>
            <button id="print-button" onclick="window.print();">Print</button>
            <div id="content">' . $html . '</div>
        </body>
        </html>';

        echo $html;
    }

    // End
     // Edited by Akshay on 10-2-2025
     public function getEmployeesforABS($branch_code = '')
     {
         $condition = ($branch_code != '') ? " where ed.branch_code =  '$branch_code'" : '';
 
         $this->Documents->useDbConfig = $this->Session->read('ds');
         try{
             $result = $this->Documents->query("select `ed`.`emp_pkey` AS `emp_pkey`,`ed`.`guradian` as guardian,`ed`.`blood` as blood,
             concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,'')) AS `emp_name`,`ep`.`emp_type` as `emp_type`,
             `ep`.`emp_company_id` AS `employee_id`,`br`.`branch_name` AS `branch`,`br`.`address` AS `branch_address`,
             `ep`.`notice_days` as `notice_days`,`ed`.`address` as `address`,`ed`.`city` as `city`,`ed`.`state` as `state`,`ed`.`pincode` as `pincode`,`ed`.`mobile_no` as `mobile_no`,`ed`.`email` as `email`,`ed`.`classification` as `gender`,`ed`.`maritual_status` as `maritual_status`,`ed`.`education` as `education`,`ed`.`date_of_birth` as `dob`,`ed`.`bank_name` as `bank_name`,`ed`.`branch_name` as `branch_name`,`ed`.`branch_address` as `bank_address`,`ed`.`ifsc_code` as `ifsc_code`,`ed`.`account_no` as `account_no`,
             `designation`.`desig_name` AS `designation`,
             `department`.`dept_name` AS `department`,`ep`.`joining_date` AS `joining_date`, `ep`.`attr2` AS `probation_days`,
              `uc`.`avatar` AS `avatar`
              from ((((`emp_proff` `ep` join `emp_details` `ed` on((`ed`.`emp_pkey` = `ep`.`emp_fkey`) and `ed`.`status` = 1)) 
              left join `branches` `br` on((`ed`.`branch_code` = `br`.`branch_code`) and (`br`.`status` = 1))) 
              left join `designation` on(((`ep`.`designation` = `designation`.`desig_code`) 
              and (`designation`.`status` = 1)))) 
              left join `department` on(((`ep`.`emp_dept` = `department`.`dept_code`) and (`department`.`status` = 1)))
              left join `user_credentials` `uc` ON (`ep`.`emp_fkey` = `uc`.`emp_fkey`)
              ) " . $condition . " order by ed.first_name");
         }catch(Exception $e){debug($e);}
 
         // var_dump($result);
         $employees = [];
         foreach ($result as $key => $val) {
             $employees[] =  array_merge($val['ed'], $val[0], $val['ep'], $val['br'], $val['designation'], $val['department'], $val['uc']);
         }
         return $employees;
     }
     // End
}
