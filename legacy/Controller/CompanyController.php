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
class CompanyController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Company';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'Units', 'ComplianceInfo', 'PolicyInfo','Menu');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index() {
       
        //edited by athira on 04-02-2024
        $this->Menu->useDbConfig = $this->Session->read('ds');
        $plan=$this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
        $this->set('plan',$plan);
        //end
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        
    }
    
    public function initialsetup(){
        
    }
    
    public function setup(){
        
    }
    
    public function viewinfo(){
        
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set("contactinfo", $arr_comp_contact_info['CompanyContactInfo']);
  //   $this->set("contactinfo", isset($arr_comp_contact_info['CompanyContactInfo'])?$arr_comp_contact_info['CompanyContactInfo']:'');
        $data = array();
        $data["cinno"] = "";
        $data["panno"] = "";
        $data["servicetax"] = "";
        $data["tanno"] = "";
        $data["pfno"] = "";
        $data["empstateinsno"] = "";
        $data["ptnoco"] = "";
        $data["ptnodir"] = "";
        $data["ptnoemp"] = ""; // array to pass back data
        $this->ComplianceInfo->useDbConfig = $this->Session->read('ds');

        $arr_comp_complianceinfo_db = $this->ComplianceInfo->find('first');
        if (isset($arr_comp_complianceinfo_db['ComplianceInfo'])) {
            $arr_comp_complianceinfo = $arr_comp_complianceinfo_db['ComplianceInfo'];

            $data["cinno"] = $arr_comp_complianceinfo["cin_no"];
            $data["panno"] = $arr_comp_complianceinfo["pan_no"];
            $data["servicetax"] = $arr_comp_complianceinfo["service_tax"];
            $data["tanno"] = $arr_comp_complianceinfo["tan_no"];
            $data["pfno"] = $arr_comp_complianceinfo["pf_no"];
            $data["empstateinsno"] = $arr_comp_complianceinfo["emp_state_ins_no"];
            $data["ptnoco"] = $arr_comp_complianceinfo["pt_no_co"];
            $data["ptnodir"] = $arr_comp_complianceinfo["pt_no_dir"];
            $data["ptnoemp"] = $arr_comp_complianceinfo["pt_no_emp"];
        }

        $this->set("complianceInfo", $data);
          // edited by bindhu 26-03-2026
         $res = $this->UserCredentials->query("SELECT attendance_format, attendance_date, payroll_type FROM db_config WHERE active = 'Y' LIMIT 1");
        $db_config = array();
        if (!empty($res)) {
            
            $db_config = isset($res[0]['db_config']) ? $res[0]['db_config'] : (isset($res[0][0]) ? $res[0][0] : array());
        }
        $this->set('db_config', $db_config);
        // edited by bindhu 26-03-2026 end
    }
        public function infoedit()   {
            $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set("contactinfo", $arr_comp_contact_info['CompanyContactInfo']);

        $data = array();
        $data["cinno"] = "";
        $data["panno"] = "";
        $data["servicetax"] = "";
        $data["tanno"] = "";
        $data["pfno"] = "";
        $data["empstateinsno"] = "";
        $data["ptnoco"] = "";
        $data["ptnodir"] = "";
        $data["ptnoemp"] = ""; // array to pass back data
        $this->ComplianceInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_complianceinfo_db = $this->ComplianceInfo->find('first');
        if (isset($arr_comp_complianceinfo_db['ComplianceInfo'])) {
            $arr_comp_complianceinfo = $arr_comp_complianceinfo_db['ComplianceInfo'];

            $data["cinno"] = $arr_comp_complianceinfo["cin_no"];
            $data["panno"] = $arr_comp_complianceinfo["pan_no"];
            $data["servicetax"] = $arr_comp_complianceinfo["service_tax"];
            $data["tanno"] = $arr_comp_complianceinfo["tan_no"];
            $data["pfno"] = $arr_comp_complianceinfo["pf_no"];
            $data["empstateinsno"] = $arr_comp_complianceinfo["emp_state_ins_no"];
            $data["ptnoco"] = $arr_comp_complianceinfo["pt_no_co"];
            $data["ptnodir"] = $arr_comp_complianceinfo["pt_no_dir"];
            $data["ptnoemp"] = $arr_comp_complianceinfo["pt_no_emp"];
        }

        $this->set("complianceInfo", $data);
          // edited by bindhu 26-03-2026
         $db_config = $this->UserCredentials->query("SELECT attendance_format, attendance_date, payroll_type FROM db_config WHERE active = 'Y' LIMIT 1");
        $this->set('db_config', !empty($db_config) ? $db_config[0]['db_config'] : array());
        // edited by bindhu 26-03-2026 end
    
        } 

    public function contactinfo() {

        $this->autoRender = FALSE;
        $this->layout = null;
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        if (isset($arr_comp_contact_info['CompanyContactInfo'])) {
            echo json_encode($arr_comp_contact_info['CompanyContactInfo']);
        }
    }

    public function loadContactInfo() {
        $this->autoRender = FALSE;

        $errors = array();   // array to hold validation errors
        $data = array();   // array to pass back data
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        if (isset($arr_comp_contact_info['CompanyContactInfo'])) {

            echo json_encode(array("success" => true, "data" => $arr_comp_contact_info['CompanyContactInfo']));
        } else { {

                echo json_encode(array());
            }
        }
    }

//     public function savecompanysetup() {
        
// 		//Windows server
// 		//$dirsep = "\/";
// 		//Linux server
// 		$dirsep = "/";
		
//         $this->autoRender = FALSE;

//         $errors = array();   // array to hold validation errors
//         $data = array();   // array to pass back data
//         $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

//         $arr_form_data = $this->request->data;

//         $arr_comp_contact_info = $this->CompanyContactInfo->find('first', array('fields' => array('id')));
//         if (isset($arr_comp_contact_info['CompanyContactInfo']['id']) && $arr_comp_contact_info['CompanyContactInfo']['id'] != '') {
//             $arr_form_data['id'] = $arr_comp_contact_info['CompanyContactInfo']['id'];
//         }
//         //$result	=	$this->CompanyContactInfo->save($arr_form_data);



//         try {
//             $companycode = $this->Session->read('company_code');
//             $cwd_path = getcwd().$dirsep;
//             $file_webroot_path = "files" . $dirsep . "companylogos" . $dirsep . $companycode . $dirsep;
//             if(!file_exists($cwd_path.$file_webroot_path)){
//                 mkdir($cwd_path.$file_webroot_path, 0755, TRUE);
//             }
            
//             if (!isset($_FILES['companylogofile']['error']) || is_array($_FILES['companylogofile']['error'])) {
//                 throw new RuntimeException('Invalid parameters.');
//             }

//             // Check $_FILES['upfile']['error'] value.
//             switch ($_FILES['companylogofile']['error']) {
//                 case UPLOAD_ERR_OK:
//                     break;
//                 case UPLOAD_ERR_NO_FILE:
//                     throw new RuntimeException('No file sent.');
//                 case UPLOAD_ERR_INI_SIZE:
//                 case UPLOAD_ERR_FORM_SIZE:
//                     throw new RuntimeException('Exceeded filesize limit.');
//                 default:
//                     throw new RuntimeException('Unknown errors.');
//             }

//             // You should also check filesize here. 
//             if ($_FILES['companylogofile']['size'] > 1000000) {
//                 throw new RuntimeException('Exceeded filesize limit.');
//             }

//             // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
//             // Check MIME Type by yourself.
//             $finfo = new finfo(FILEINFO_MIME_TYPE);
//             if (false === $ext = array_search(
//                     $finfo->file($_FILES['companylogofile']['tmp_name']), array(
//                 'jpg' => 'image/jpeg',
//                 'png' => 'image/png',
//                 'gif' => 'image/gif',
//                     ), true
//                     )) {
//                 throw new RuntimeException('Invalid file format.');
//             }

//             // You should name it uniquely.
//             // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
//             // On this example, obtain safe unique name from its binary data.
//             //$filename = sprintf('img/companylogos/' . $this->Session->read('company_code') . '/%s.%s', sha1_file($_FILES['companylogofile']['tmp_name']), $ext);
//             $filename = sprintf('%s.%s',sha1_file($_FILES['companylogofile']['tmp_name']),$ext);
            
//             if (!move_uploaded_file($_FILES['companylogofile']['tmp_name'], $cwd_path.$file_webroot_path.$filename)) {
//                 throw new RuntimeException('Failed to move uploaded file.');
//             }
//             $arr_form_data["logo"] = $file_webroot_path.$filename;
//             $this->Session->write('company_logo',$file_webroot_path.$filename);
            
//             //   echo 'File is uploaded successfully.';
//         } catch (RuntimeException $e) {

//             //  echo $e->getMessage();
//         }
//         $result = $this->CompanyContactInfo->save($arr_form_data);
//         if (empty($result)) {

//             $data['success'] = false;
//             $data['errors'] = "Contact Information can not save now";
//         } else {

//             // if there are no errors, return a message
//             $data['success'] = true;
//             $data['msg'] = 'Contact Information  Successfully saved!';
//         }
//         echo json_encode($data);
        
//         $data = $this->request->data;

// //	debug($_REQUEST);
//         $arr_form_data["cin_no"] = $data["cinno"];
//         $arr_form_data["pan_no"] = $data["panno"];
//         $arr_form_data["service_tax"] = $data["servicetax"];
//         $arr_form_data["tan_no"] = $data["tanno"];
//         $arr_form_data["pf_no"] = $data["pfno"];
//         $arr_form_data["emp_state_ins_no"] = $data["empstateinsno"];
//         $arr_form_data["pt_no_co"] = $data["ptnoco"];
//         $arr_form_data["pt_no_dir"] = $data["ptnodir"];
//         $arr_form_data["pt_no_emp"] = $data["ptnoemp"];
//         $this->ComplianceInfo->useDbConfig = $this->Session->read('ds');
//         $arr_form_data['id'] = 1;
//         $result = $this->ComplianceInfo->save($arr_form_data);
//         $this->autoRender = FALSE;
//         $resp = array();
//         $resp["success"] = true;
//         $resp["msg"] = "Compliance info saved successfully";

//         echo json_encode($resp);
//     }

public function savecompanysetup()
    {

        //Windows server
        //$dirsep = "\/";
        //Linux server
        $dirsep = "/";

        $this->autoRender = FALSE;

        $errors = array(); // array to hold validation errors
        $data = array(); // array to pass back data
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first', array('fields' => array('id')));
        if (isset($arr_comp_contact_info['CompanyContactInfo']['id']) && $arr_comp_contact_info['CompanyContactInfo']['id'] != '') {
            $arr_form_data['id'] = $arr_comp_contact_info['CompanyContactInfo']['id'];
        }
        //$result	=	$this->CompanyContactInfo->save($arr_form_data);



        try {
            $companycode = $this->Session->read('company_code');
            $cwd_path = getcwd() . $dirsep;
            $file_webroot_path = "files" . $dirsep . "companylogos" . $dirsep . $companycode . $dirsep;
            if (!file_exists($cwd_path . $file_webroot_path)) {
                mkdir($cwd_path . $file_webroot_path, 0755, TRUE);
            }

            if (!isset($_FILES['companylogofile']['error']) || is_array($_FILES['companylogofile']['error'])) {
                throw new RuntimeException('Invalid parameters.');
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['companylogofile']['error']) {
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
            if ($_FILES['companylogofile']['size'] > 1000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
            $finfo->file($_FILES['companylogofile']['tmp_name']), array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            ), true
            )) {
                throw new RuntimeException('Invalid file format.');
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            //$filename = sprintf('img/companylogos/' . $this->Session->read('company_code') . '/%s.%s', sha1_file($_FILES['companylogofile']['tmp_name']), $ext);
            $filename = sprintf('%s.%s', sha1_file($_FILES['companylogofile']['tmp_name']), $ext);

            if (!move_uploaded_file($_FILES['companylogofile']['tmp_name'], $cwd_path . $file_webroot_path . $filename)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            $arr_form_data["logo"] = $file_webroot_path . $filename;
            $this->Session->write('company_logo', $file_webroot_path . $filename);

        //   echo 'File is uploaded successfully.';
        }
        catch (RuntimeException $e) {

        //  echo $e->getMessage();
        }
        $result = $this->CompanyContactInfo->save($arr_form_data);
        if (empty($result)) {

            $data['success'] = false;
            $data['errors'] = "Contact Information can not save now";
        }
        else {
            $data['success'] = true;
            $data['msg'] = 'Information successfully saved!';
        }

        $form_data = $this->request->data;
        //	debug($_REQUEST);
                $arr_form_data["cin_no"] = isset($form_data["cinno"]) ? $form_data["cinno"] : '';
                $arr_form_data["pan_no"] = isset($form_data["panno"]) ? $form_data["panno"] : '';
                $arr_form_data["service_tax"] = isset($form_data["servicetax"]) ? $form_data["servicetax"] : '';
                $arr_form_data["tan_no"] = isset($form_data["tanno"]) ? $form_data["tanno"] : '';
                $arr_form_data["pf_no"] = isset($form_data["pfno"]) ? $form_data["pfno"] : '';
                $arr_form_data["emp_state_ins_no"] = isset($form_data["empstateinsno"]) ? $form_data["empstateinsno"] : '';
                $arr_form_data["pt_no_co"] = isset($form_data["ptnoco"]) ? $form_data["ptnoco"] : '';
                $arr_form_data["pt_no_dir"] = isset($form_data["ptnodir"]) ? $form_data["ptnodir"] : '';
                $arr_form_data["pt_no_emp"] = isset($form_data["ptnoemp"]) ? $form_data["ptnoemp"] : '';
        $this->ComplianceInfo->useDbConfig = $this->Session->read('ds');
        $arr_form_data['id'] = 1;
        $result = $this->ComplianceInfo->save($arr_form_data);
        $this->autoRender = FALSE;

        // Update db_config for Attendance and Salary cycle
        $att_cycle = isset($form_data['attendance_cycle']) ? (int)$form_data['attendance_cycle'] : 1;
        $sal_cycle = isset($form_data['salary_cycle']) ? $form_data['salary_cycle'] : 'T';

        if ($att_cycle == 1) {
            $attendance_format = 'B';
            $attendance_date = 0;
        }
        else {
            $attendance_format = 'A';
            $attendance_date = $att_cycle;
        }

        $sql = "UPDATE db_config SET 
                attendance_format = '$attendance_format', 
                attendance_date = $attendance_date, 
                payroll_type = '$sal_cycle' 
                WHERE active = 'Y'";

        $this->UserCredentials->query($sql);

        $this->autoRender = FALSE;
        echo json_encode($data);
    }
    
    public function getCompanyComplianceInfo() {
        $this->autoRender = FALSE;

        $errors = array();   // array to hold validation errors
        $data = array();
        $data["cinno"] = "";
        $data["panno"] = "";
        $data["servicetax"] = "";
        $data["tanno"] = "";
        $data["pfno"] = "";
        $data["empstateinsno"] = "";
        $data["ptnoco"] = "";
        $data["ptnodir"] = "";
        $data["ptnoemp"] = ""; // array to pass back data
        $this->ComplianceInfo->useDbConfig = $this->Session->read('ds');

        $arr_comp_complianceinfo_db = $this->ComplianceInfo->find('first');
        if (isset($arr_comp_complianceinfo_db['ComplianceInfo'])) {
            $arr_comp_complianceinfo = $arr_comp_complianceinfo_db['ComplianceInfo'];

            $data["cinno"] = $arr_comp_complianceinfo["cin_no"];
            $data["panno"] = $arr_comp_complianceinfo["pan_no"];
            $data["servicetax"] = $arr_comp_complianceinfo["service_tax"];
            $data["tanno"] = $arr_comp_complianceinfo["tan_no"];
            $data["pfno"] = $arr_comp_complianceinfo["pf_no"];
            $data["empstateinsno"] = $arr_comp_complianceinfo["emp_state_ins_no"];
            $data["ptnoco"] = $arr_comp_complianceinfo["pt_no_co"];
            $data["ptnodir"] = $arr_comp_complianceinfo["pt_no_dir"];
            $data["ptnoemp"] = $arr_comp_complianceinfo["pt_no_emp"];
        }


        echo json_encode(array("success" => true, "data" => $data));
    }

    public function saveCompanyComplianceInfo() {

        $data = $this->request->data;

//	debug($_REQUEST);
        $arr_form_data["cin_no"] = $data["cinno"];
        $arr_form_data["pan_no"] = $data["panno"];
        $arr_form_data["service_tax"] = $data["servicetax"];
        $arr_form_data["tan_no"] = $data["tanno"];
        $arr_form_data["pf_no"] = $data["pfno"];
        $arr_form_data["emp_state_ins_no"] = $data["empstateinsno"];
        $arr_form_data["pt_no_co"] = $data["ptnoco"];
        $arr_form_data["pt_no_dir"] = $data["ptnodir"];
        $arr_form_data["pt_no_emp"] = $data["ptnoemp"];
        $this->ComplianceInfo->useDbConfig = $this->Session->read('ds');
        $arr_form_data['id'] = 1;
        $result = $this->ComplianceInfo->save($arr_form_data);
        $this->autoRender = FALSE;
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Compliance info saved successfully";

        echo json_encode($resp);
    }

    public function savePolicy() {
        if (isset($_POST['finalisedDate'])) {
            $this->autoRender = FALSE;
            $data['policy_key'] = 1;
            $data['policy_name'] = "Finalised Date";
            $data['policy_value'] = $_POST['finalisedDate'];


            $this->PolicyInfo->useDbConfig = $this->Session->read('ds');
            $this->PolicyInfo->save($data);
        }
        if (isset($_POST['salayPay'])) {

            $data['policy_key'] = 2;
            $data['policy_name'] = "Salary Pay";
            $data['policy_value'] = $_POST['salayPay'];


            $this->PolicyInfo->useDbConfig = $this->Session->read('ds');
            $this->PolicyInfo->save($data);
        }
        if (isset($_POST['proofdate'])) {

            $data['policy_key'] = 3;
            $data['policy_name'] = "Proof Date";
            $data['policy_value'] = $_POST['proofdate'];


            $this->PolicyInfo->useDbConfig = $this->Session->read('ds');
            $this->PolicyInfo->save($data);
        }
        if (isset($_POST['yesno'])) {

            $data['policy_key'] = 4;
            $data['policy_name'] = "Yes No";
            $data['policy_value'] = $_POST['yesno'];


            $this->PolicyInfo->useDbConfig = $this->Session->read('ds');
            $this->PolicyInfo->save($data);
        }

        if (isset($_POST['payrollfrom'])) {

            $data['policy_key'] = 5;
            $data['policy_name'] = "Payroll From";
            $data['policy_value'] = $_POST['payrollfrom'];


            $this->PolicyInfo->useDbConfig = $this->Session->read('ds');
            $this->PolicyInfo->save($data);
        }
    }

    public function getPolicyInfo() {
        $this->autoRender = FALSE;

        $errors = array();   // array to hold validation errors
        $data = array();
        $data["finalisedDate"] = "Last Day";
        $data["salayPay"] = "1";
        $data["proofdate"] = date("d/m/Y");
        $data["yesno"] = "Yes";
        $data["payrollfrom"] = "July 2014"; // array to pass back data

        $this->PolicyInfo->useDbConfig = $this->Session->read('ds');

        $arr_comp_complianceinfo_db = $this->PolicyInfo->find('all');
        foreach ($arr_comp_complianceinfo_db as $key => $value) {
            if ($value['PolicyInfo']['policy_key'] == 1) {
                $data["finalisedDate"] = $value['PolicyInfo']['policy_value'];
            }
            if ($value['PolicyInfo']['policy_key'] == 2) {
                $data["salayPay"] = $value['PolicyInfo']['policy_value'];
            }
            if ($value['PolicyInfo']['policy_key'] == 3) {
                $data["proofdate"] = $value['PolicyInfo']['policy_value'];
            }
            if ($value['PolicyInfo']['policy_key'] == 4) {
                $data["yesno"] = $value['PolicyInfo']['policy_value'];
            }
            if ($value['PolicyInfo']['policy_key'] == 5) {
                $data["payrollfrom"] = $value['PolicyInfo']['policy_value'];
            }
        }
        echo json_encode($data);
    }

}
