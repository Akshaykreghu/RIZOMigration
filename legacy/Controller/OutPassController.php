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
ini_set('max_execution_time', 2000);
ini_set('memory_limit', '1024M');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class OutPassController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'OutPass';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('DocTemplate', 'TemplatesDetails', 'Templates', 'Documents', 'EmpDetails', 'Units', 'EmployeeDetails', 'EmployeeExpenses', 'DocumentAllocation', 'DocumentUpload', 'GatePass', 'GatePassItems', 'OutPass');
    public $components = array('MasterdataManagement');

    public function index()
    {
    }
    function pagination($data)
    {
        $limit           = (isset($data['rows']) && !empty($data['rows'])) ? "  limit  " . $data['rows'] . "" : "";
        $offset          = (isset($data['page']) && !empty($data['page'])) ? " offset  " . (($data['page'] - 1)  * $data['rows']) . "" : "";
        return array("limit" => $limit, "offset" => $offset);
    }


    public function uploadForm($pkey = 0)
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->GatePassItems->useDbConfig = $this->Session->read('ds');
        date_default_timezone_set('Asia/Kolkata');
        $currentYear = date('Y');

        $arr_out_pass = array();
        $arr_item = array();
        try {


            $arr_emp = $this->EmployeeDetails->query("SELECT ei.emp_pkey, ei.EmpName, ei.employee_id 
                                                        FROM employee_info ei 
                                                        WHERE ei.emp_status = 1 
                                                        ORDER BY ei.EmpName;
                                                        ");
            $this->set("arr_emp", $arr_emp);
            $this->set("pkey", $pkey);
            if ($pkey == 0) {
                $arr_pass_pkey = $this->GatePass->query("SELECT COALESCE(MAX(out_pass_pkey), 0) AS latest_pkey FROM out_pass");
                $pass_pkey = isset($arr_pass_pkey[0][0]['latest_pkey']) ? $arr_pass_pkey[0][0]['latest_pkey'] : 0;
                $pass_pkey = $pass_pkey + 1;
                $pass_pkey = sprintf("%03d", $pass_pkey);
                $pass_no = $currentYear . '/' . ($pass_pkey);
                $arr_out_pass[0]['GatePass']['out_pass_number'] = $pass_no;
            } else {

                $arr_out_pass = $this->GatePass->find('all', [
                    'fields' => ['GatePass.*, gi.*'],
                    'conditions' => ['GatePass.status' => 1, 'GatePass.gate_pass_pkey' => $pkey],
                    'joins' => [
                        [
                            'table' => 'gate_pass_items',
                            'alias' => 'gi',
                            'type' => 'LEFT',
                            'conditions' => 'gi.gate_pass_items_pkey = GatePass.gate_pass_items_fkey'
                        ]
                    ],
                    'order' => ['GatePass.gate_pass_pkey']
                ]);

                $arr_item = $this->GatePassItems->find('all', [
                    'fields' => ['GatePassItems.*'],
                    'conditions' => ['GatePassItems.status' => 1, 'GatePassItems.gate_pass_fkey' => $pkey],
                    'order' => ['GatePassItems.gate_pass_items_pkey']
                ]);

                //debug($arr_out_pass);

            }
        } catch (Exception $e) {
            debug($e);
        }
        // debug($arr_out_pass);

        $arr_out_pass = $arr_out_pass[0]['GatePass'];

        $this->set("arr_item", $arr_item);
        $this->set("arr_out_pass", $arr_out_pass);
    }
    public function getDocumentsFromDatabase()
    {
        $this->autoRender = false;
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->OutPass->useDbConfig = $this->Session->read('ds');
        $livesite = $this->webroot;
        $this->set('livesite', $livesite);
        $userGroup = $this->Session->read('user_group');
        $loginUser = $this->Session->read('login_user_id');

        $arr_data = $this->request->data;

        $page = isset($arr_data['page']) ? $arr_data['page'] : 1;
        $rows = isset($arr_data['rows']) ? $arr_data['rows'] : 10;
        $number = isset($arr_data['out_pass_number'])? (string)$arr_data['out_pass_number']:'';
        // debug($pkey);
        if (true) {
            if($number !== ''){
                $conditions = [
                    'OR' => [
                        'OutPass.out_pass_number LIKE' => '%' . $number . '%',
                        'OutPass.out_pass_date LIKE' => '%' . $number . '%',
                        'OutPass.type LIKE' => '%' . $number . '%',
                        'OutPass.creation_date LIKE' => '%' . $number . '%',
                        'ei.EmpName LIKE' => '%' . $number . '%',
                    ],
                    'OutPass.status !=' => 0,
                ];
            }else{
                $conditions['OutPass.status !='] = 0;
            }
            // debug($conditions);
            $formattedData =array();
            $data = $this->OutPass->find('all', [
                'conditions' => $conditions,
                'fields' => [
                    'OutPass.out_pass_pkey',
                    'OutPass.out_pass_number',
                    'OutPass.type',
                    'OutPass.staff_pkey',
                    'OutPass.out_pass_date',
                    'OutPass.creation_date',
                    'ei.EmpName', // Add this field for EmpName
                    'ei.emp_pkey',
                    'ei.employee_id',
                    'OutPass.status'
                ],
                'joins' => [
                    [
                        'table' => 'employee_info',
                        'alias' => 'ei',
                        'type' => 'LEFT',
                        'conditions' => [
                            'ei.emp_pkey = OutPass.staff_pkey',
                        ],
                    ],
                ],
                'order' => ['OutPass.out_pass_pkey' => 'DESC']
            ]);
        }

        // debug($data); exit;

        foreach ($data as $row) {
            $gatePkey =  $row['OutPass']['out_pass_pkey'];
            $type = $row['OutPass']['type'];
            $pass = false;
            $status = $row['OutPass']['status'];

            $formattedData[] = [
                'out_pass_pkey' => $row['OutPass']['out_pass_pkey'],
                'out_pass_number' => $row['OutPass']['out_pass_number'],
                'status' => $row['OutPass']['status'],
                'type' => $row['OutPass']['type'],
                'out_pass_date' => $row['OutPass']['out_pass_date'],
                'creation_date' => $row['OutPass']['creation_date'],
                'issued_to' => $row['ei']['EmpName'].' - ' .$row['ei']['employee_id'],
                'issued_to_pkey' => $row['ei']['emp_pkey'],
                // 'download_link' => "<a href='javascript:void(0)' onclick=\"loadPDFPreview('{$pdfPath}','{$type}','{$fileName}')\"><button>View</button></a>"
                'download_link' => "<a href='javascript:void(0)' onclick=\"printOutPassPDF('{$gatePkey}', '{$pass}', '{$type}', '{$status}')\"><button>Download</button></a>"
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

    public function savePass($pkey = 0)
    {
        $this->autoRender = false;
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $this->OutPass->useDbConfig = $this->Session->read('ds');
        $this->GatePassItems->useDbConfig = $this->Session->read('ds');
        $this->OutPass->useDbConfig = $this->Session->read('ds');

        $user_id = $this->Session->read('login_user_id');
        try{
        // Assuming the request is a POST request
        if ($this->request->is('post')) {
            $postData = $this->request->data;

            $data['out_pass_number'] = isset($postData['out_pass_number']) ? $postData['out_pass_number'] : '';
            $data['out_pass_date'] = isset($postData['out_pass_date']) ? $postData['out_pass_date'] : '';
            $data['type'] = isset($postData['type']) ? $postData['type'] : '';
            $data['staff_pkey'] = isset($postData['staff_pkey']) ? $postData['staff_pkey'] : '';
            $data['out_time'] = isset($postData['out_time']) ? $postData['out_time'] : '';
            $data['in_time'] = isset($postData['in_time']) ? $postData['in_time'] : '';
            $data['remarks'] = isset($postData['remarks']) ? $postData['remarks'] : '';
            $data['sanctioned_by'] = isset($postData['sanctioned_by']) ? $postData['sanctioned_by'] : '';
            $data['issued_by'] = isset($postData['issued_by']) ? $postData['issued_by'] : '';

            // debug($data); exit;
            $arr_out_pass = $this->OutPass->query("SELECT DISTINCT out_pass_pkey FROM out_pass WHERE status = 1");

            $arr_out_pass = isset($arr_out_pass[0])? $arr_out_pass[0]: array();


            // debug($item); exit;
            $saveData = array();
            // debug($pkey);
            // debug($_POST); exit;

            // $originalDate = isset($data['issued_date']) ? $data['issued_date'] : '00/00/0000';
            // //$date = DateTime::createFromFormat('m/d/Y', $originalDate);
            date_default_timezone_set('Asia/Kolkata');
            $data['creation_date'] = date('Y-m-d H:i:s');
            $data['created_by'] = $user_id;

            $save = false;
            if ($pkey == 0) {
               // debug($data); exit;
                $save = $this->OutPass->save($data);
                
                $gatePassPkey = $this->OutPass->id;

            }
            //debug($save);exit;
            if ($save !== false) {
                // Success response
                $response = [
                    'status' => 'success',
                    'message' => 'Out pass saved successfully.',
                    'pkey' => $gatePassPkey
                ];
            } else {
                // Failure response
                $response = [
                    'status' => 'error',
                    'message' => 'Error saving gate pass. Please try again.'
                ];
            }
        } else {
            // Invalid request type
            $response = [
                'status' => 'error',
                'message' => 'Invalid request type.'
            ];
        }

        // Return JSON response
        $this->response->type('json');
        echo json_encode($response);
    }catch(Exception $e){
        debug($e);exit;
    }
    }

    public function savePreview($pkey = 0, $status = 0, $edit = false)
    {
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->GatePassItems->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('pkey',  $pkey);
        $this->set('status', $status);
        $this->set('edit',  $edit);

        try {
            $arr_out_pass = $this->GatePass->query("SELECT
            OutPass.*,
            staff_pkey.EmpName AS staff_name,
            issued_by.EmpName AS issued_by_name,
            sanctioned_by.EmpName AS sanctioned_by_name
        FROM
            out_pass AS OutPass
        LEFT JOIN
            employee_info AS staff_pkey ON OutPass.staff_pkey = staff_pkey.emp_pkey
        LEFT JOIN
            employee_info AS sanctioned_by ON OutPass.sanctioned_by = sanctioned_by.emp_pkey
        LEFT JOIN
            employee_info AS issued_by ON OutPass.issued_by = issued_by.emp_pkey
        WHERE
            OutPass.out_pass_pkey = '$pkey'
        ORDER BY
            OutPass.out_pass_pkey;
        ");
        // debug($arr_out_pass);
            $arr_out_pass = isset($arr_out_pass[0]['OutPass']) ? $arr_out_pass[0]['OutPass'] : array();
            // debug($arr_out_pass);
            $this->set('arr_out_pass', $arr_out_pass);

            $arr_emp = $this->GatePass->query("SELECT ei.emp_pkey, ei.EmpName, ei.employee_id 
            FROM employee_info ei 
            WHERE ei.emp_status = 1 
            ORDER BY ei.EmpName;
            ");
            $this->set("arr_emp", $arr_emp);
        } catch (Exception $e) {
            debug($e);
            exit;
        }


        // debug($arr_out_pass);

    }

    public function printPass($pkey = 0, $is_edited = 'false', $pass = 'false')
    {
        try {


            $this->autoRender = false;
            $this->DocTemplate->useDbConfig = $this->Session->read('ds');
            $this->GatePass->useDbConfig = $this->Session->read('ds');
            $this->GatePassItems->useDbConfig = $this->Session->read('ds');
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);


            if ($pass == 'true') {
                $status = 1;
            } else {
                $status = 2;
            }

            if ($this->request->is('post')) {
                $postData = $this->request->data;

                if ($is_edited == 'true') {

                    // debug($is_edited);
                    $data['out_pass_number' ] = isset($postData['out_pass_number']) ? $postData['out_pass_number'] : '';
                    $data['out_pass_date'] = isset($postData['out_pass_date']) ? $postData['out_pass_date'] : '';
                    $data['type'] = isset($postData['type']) ? $postData['type'] : '';
                    $data['staff_pkey'] = isset($postData['staff_pkey']) ? $postData['staff_pkey'] : '';
                    $data['out_time'] = isset($postData['out_time']) ? $postData['out_time'] : '';
                    $data['in_time'] = isset($postData['in_time']) ? $postData['in_time'] : '';
                    $data['remarks'] = isset($postData['remarks']) ? $postData['remarks'] : '';
                    $data['sanctioned_by'] = isset($postData['sanctioned_by']) ? $postData['sanctioned_by'] : '';
                    $data['issued_by'] = isset($postData['issued_by']) ? $postData['issued_by'] : '';


                    // debug($item);exit;
                } else {

                    $save = $this->GatePass->query("UPDATE `out_pass` AS `OutPass`
                                                    SET
                                                        `OutPass`.`status` = '$status'
                                                    WHERE
                                                        `OutPass`.`out_pass_pkey` = $pkey;
                                                    ");
                }


                // debug($item); exit;
                $saveData = array();
                // debug($pkey);
                // debug($_POST); exit;

                $originalDate = isset($data['issued_date']) ? $data['issued_date'] : '00/00/0000';
                //$date = DateTime::createFromFormat('m/d/Y', $originalDate);
                $data['issued_date'] =  date('Y-m-d', strtotime($originalDate));

                if ($is_edited == 'true') {
                    $user_id = $this->Session->read('login_user_id');
                    date_default_timezone_set('Asia/Kolkata');
                    $date_time = date('Y-m-d H:i:s');
                    try {

                        $out_pass_number = $data['out_pass_number' ];
                        $out_pass_date = $data['out_pass_date'] ;
                        $type =  $data['type'];
                        $staff_pkey =  $data['staff_pkey'];
                        $out_time = $data['out_time'];
                        $in_time = $data['in_time'];
                        $remarks = $data['remarks'];
                        $sanctioned_by =  $data['sanctioned_by'];
                        $issued_by = $data['issued_by'] ;



                        $save = $this->GatePass->query("UPDATE `out_pass` AS `OutPass`
                                                        SET
                                                            `OutPass`.`out_pass_number` = '$out_pass_number',
                                                            `OutPass`.`out_pass_date` = '$out_pass_date',
                                                            `OutPass`.`type` = '$type',
                                                            `OutPass`.`staff_pkey` = ' $staff_pkey',
                                                            `OutPass`.`out_time` =  '$out_time',
                                                            `OutPass`.`in_time` = '$in_time',
                                                            `OutPass`.`remarks` = '$remarks',
                                                            `OutPass`.`sanctioned_by` = '$sanctioned_by',
                                                            `OutPass`.`issued_by` = '$issued_by',
                                                            `OutPass`.`modified_by` = '$user_id',
                                                            `OutPass`.`modified_date` = '$date_time',
                                                            `OutPass`.`status` = '$status'

                                                        WHERE
                                                            `OutPass`.`out_pass_pkey` = $pkey;
                                                        ");
                    } catch (Exception $e) {
                        debug($e);
                        exit;
                    }
                }
                //debug($save);exit;
                if ($save !== false) {
                    // Success response
                    $response = [
                        'status' => 'success',
                        'message' => 'Gate pass saved successfully.',
                        'pkey' => $pkey
                    ];
                } else {
                    // Failure response
                    $response = [
                        'status' => 'error',
                        'message' => 'Error saving gate pass. Please try again.'
                    ];
                }
            } else {
                // Invalid request type
                $response = [
                    'status' => 'error',
                    'message' => 'Invalid request type.'
                ];
            }

            // Return JSON response
            $this->response->type('json');
        } catch (Exception $e) {
            // debug($e);exit;
        }

        // JSON response (after PDF is generated)
        echo json_encode($response);
    }

    public function printOutPass($pkey = 0, $pass = 'false')
    {
        $this->autoRender = false;
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        try {
            if ($pass == 'false') {
                $arr_out_pass = $this->GatePass->query("SELECT
                OutPass.*,
                staff_pkey.EmpName AS staff_name,
                staff_pkey.employee_id AS staff_id,

                issued_by.EmpName AS issued_by_name,
                issued_by.employee_id AS issued_by_id,

                sanctioned_by.EmpName AS sanctioned_by_name,
                sanctioned_by.employee_id AS sanctioned_by_id
            FROM
                out_pass AS OutPass
            LEFT JOIN
                employee_info AS staff_pkey ON OutPass.staff_pkey = staff_pkey.emp_pkey
            LEFT JOIN
                employee_info AS sanctioned_by ON OutPass.sanctioned_by = sanctioned_by.emp_pkey
            LEFT JOIN
                employee_info AS issued_by ON OutPass.issued_by = issued_by.emp_pkey
            WHERE
                OutPass.out_pass_pkey = '$pkey'
            ORDER BY
                OutPass.out_pass_pkey;
            ");

                 $arr_out_pass[0]['OutPass']['staff_name'] = isset($arr_out_pass[0]['staff_pkey']['staff_name'])? $arr_out_pass[0]['staff_pkey']['staff_name'].' - '.$arr_out_pass[0]['staff_pkey']['staff_id']:'';
                 $arr_out_pass[0]['OutPass']['issued_by_name'] = isset($arr_out_pass[0]['issued_by']['issued_by_name']) ? $arr_out_pass[0]['issued_by']['issued_by_name'].' - '.$arr_out_pass[0]['issued_by']['issued_by_id'] : '';
                 $arr_out_pass[0]['OutPass']['sanctioned_by_name'] = isset($arr_out_pass[0]['sanctioned_by']['sanctioned_by_name']) ? $arr_out_pass[0]['sanctioned_by']['sanctioned_by_name'].' - '.$arr_out_pass[0]['sanctioned_by']['sanctioned_by_id'] : '';    
                $arr_out_pass = isset($arr_out_pass[0]['OutPass']) ? $arr_out_pass[0]['OutPass'] : array();            


            }

            if ($pass == 'false') {

                $this->set('arr_out_pass', $arr_out_pass);


                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = trim($view->render('print_pass'));
                // echo $view_output;
                // Clear the output buffer
                // ob_clean();

                // // Output PDF using TCPDF

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('OutPass.pdf', 'D');
                $this->render('print_pass');
            }
        } catch (Exception $e) {
            debug($e);
            exit;
        }
    }


    public function deleteFromGrid($pkey = 0)
    {

        $this->autoRender = false;
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');

        try {
            $save = $this->GatePass->query("UPDATE `out_pass` AS `OutPass`
            SET
                `OutPass`.`status` = 0
    
            WHERE
                `OutPass`.`out_pass_pkey` = $pkey;
            ");

            // debug($save);


            $response = [
                'status' => 'success',
                'message' => ' out pass deleted successfully.',
                'pkey' => $pkey
            ];
        } catch (Exception $e) {
            $response = [
                'status' => 'failure',
                'message' => ' out pass delete failed.',
                'pkey' => $pkey
            ];
        }
        $this->response->type('json');
        // debug((json_encode($response))); exit;
        echo json_encode($response);
    }

    public function generatereport($pkey = 0)
    {
        $this->autoRender = false;
        $this->printOutPass($pkey);
    }

    public function checkPersonalPass($date ='', $pkey = 0){
        $this->autoRender = false;
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');

        try{
            // debug($date);
            // debug($pkey);
            $arr_emp_cur_mont = $this->GatePass->query("SELECT out_pass_pkey, status FROM out_pass
                                                        WHERE type = 'Personal' AND status != 0 AND staff_pkey = '$pkey'
                                                        AND DATE_FORMAT(out_pass_date, '%Y-%m') = DATE_FORMAT('$date', '%Y-%m')
                                                    ");

            // debug($arr_emp_cur_mont); exit;
            if(!empty($arr_emp_cur_mont)){
                $status = isset($arr_emp_cur_mont[0]['out_pass']['status'])? $arr_emp_cur_mont[0]['out_pass']['status']:'';
                if($status == 2 || $status == '2'){
                    $message = 'Personal out pass has already been issued to this employee.';
                }else{
                    $message = "Already, a personal out pass has been generated. For new creations, please delete the existing record.";
                }

                $response = [
                    'status' => 'failure',
                    'message' =>  $message,
                    'pkey' => $pkey
                ];
            }else{
                $response = [
                    'status' => 'success',
                    'message' => ' Out pass not issued in this month.',
                    'pkey' => $pkey
                ];
            }
        }catch(Exception $e){
            debug($e);exit;
        }
        $this->response->type('json');
        echo json_encode($response);



    }
}
