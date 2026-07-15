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
class ActivityController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Activity';
    public $layout = 'default';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Activity', 'Units', 'ActivityProjects', 'UserCredentials', 'CompanyContactInfo', 'EmployeeDetails', 'CentralUserCredentials');
    public $components = array('DatatablesManagement', 'MasterdataManagement');

    /*
     * Dashboard landing view
     */

    public function index() {

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $conditions = "";
        if($this->Session->read('emp_fkey')){
          $conditions = ' and (`emp_fkey` = ' . $this->Session->read('emp_fkey') . ' OR `created_by` = ' . $this->Session->read('emp_fkey') . ' )';
        }
    
        $activity_status = $this->UserCredentials->query("SELECT * FROM activity_status WHERE status = 1 ORDER BY activity_order ");
        $this->set('activity_status', $activity_status);

        $project_list = $this->UserCredentials->query("SELECT * FROM activity_projects WHERE status = 1 ");
        $this->set('project_list', $project_list);

        $employee_list = $this->UserCredentials->query("SELECT emp_pkey,first_name,last_name FROM emp_details WHERE status = 1");
        $this->set('employee_list', $employee_list);

        $activity_ids = $this->UserCredentials->query("SELECT activity_track_pkey, summary FROM activity_track WHERE status = 1 and activity_track_fkey is null $conditions");
        $this->set('activity_ids', $activity_ids);

        $resp_data = array();

    }

    public function report() {

      $this->UserCredentials->useDbConfig = $this->Session->read('ds');

      $conditions = "";
      if($this->Session->read('emp_fkey')){
        $conditions = ' and (`emp_fkey` = ' . $this->Session->read('emp_fkey') . ' OR `created_by` = ' . $this->Session->read('emp_fkey') . ' )';
      }
  
      $activity_status = $this->UserCredentials->query("SELECT * FROM activity_status WHERE status = 1 ORDER BY activity_order ");
      $this->set('activity_status', $activity_status);

      $project_list = $this->UserCredentials->query("SELECT * FROM activity_projects WHERE status = 1 ");
      $this->set('project_list', $project_list);

      $employee_list = $this->UserCredentials->query("SELECT emp_pkey,first_name,last_name FROM emp_details WHERE status = 1");
      $this->set('employee_list', $employee_list);

      $activity_ids = $this->UserCredentials->query("SELECT activity_track_pkey, summary FROM activity_track WHERE status = 1 and activity_track_fkey is null $conditions");
      $this->set('activity_ids', $activity_ids);

      $resp_data = array();

    }

    function laodReoport() {
      $this->UserCredentials->useDbConfig = $this->Session->read('ds');
      
      $conditions = "";
      $arr_form_data = $this->request->data;

      if(isset($arr_form_data['type']) && $arr_form_data['type'] != ''){
        $conditions .= " and status_fkey = " . $arr_form_data['type'] . " ";
      }
      
      if($arr_form_data['emp_pkey'] != ''){
        $conditions .= " and activity_track.emp_fkey = " . $arr_form_data['emp_pkey'] . " ";
      }

      if($arr_form_data['project'] != ''){
        $conditions .= " and activity_track.project_fkey = " . $arr_form_data['project'] . " ";
      }

      if(($arr_form_data['start_date'] != '') && ($arr_form_data['due_date'] != '')){
        $conditions .= " and start_time BETWEEN '" . $arr_form_data['start_date'] . "' and '" . $arr_form_data['due_date'] . "' ";
      }

      $activity_status = $this->UserCredentials->query("SELECT activity_track.*, atp.summary, ap.activity_projects_head, emp_details.first_name , last_name FROM activity_track LEFT JOIN activity_projects ap ON (ap.activity_projects_pkey = project_fkey) LEFT JOIN activity_track as atp ON (atp.activity_track_pkey = activity_track.activity_track_fkey) LEFT JOIN emp_details ON (emp_details.emp_pkey = activity_track.emp_fkey) WHERE activity_track.status = 1 and activity_track.activity_track_fkey is not null $conditions ORDER BY project_fkey DESC ");
      $this->set('activity_status', $activity_status);


    }

    public function projects() {

      $this->UserCredentials->useDbConfig = $this->Session->read('ds');

      $activity_status = $this->UserCredentials->query("SELECT * FROM activity_status WHERE status = 1 ORDER BY activity_order ");
      $this->set('activity_status', $activity_status);

      $project_list = $this->UserCredentials->query("SELECT * FROM activity_projects WHERE status = 1 ");
      $this->set('project_list', $project_list);

      $employee_list = $this->UserCredentials->query("SELECT emp_pkey,first_name,last_name FROM emp_details WHERE status = 1");
      $this->set('employee_list', $employee_list);

      $activity_ids = $this->UserCredentials->query("SELECT activity_track_pkey FROM activity_track WHERE status = 1");
      $this->set('activity_ids', $activity_ids);

      $resp_data = array();

    }

    public function addproject() {
        $this->ActivityProjects->useDbConfig = $this->Session->read('ds');
          
        $editData = [];
        $subtasks = [];
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != 0) {
            $editData = $this->ActivityProjects->find("first", array("conditions" => array("activity_projects_pkey" => $_REQUEST['id'])));
        }
        $this->set('editData', $editData);

        $resp_data = array();
    }

    public function add() {

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        
        $editData = [];
        $subtasks = [];
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != 0) {
            $editData = $this->UserCredentials->query("SELECT * FROM activity_track WHERE activity_track_pkey = " . $_REQUEST['id']);

            $subtasks = $this->UserCredentials->query("SELECT activity_track.*, emp_details.first_name , last_name FROM activity_track LEFT JOIN emp_details ON (emp_details.emp_pkey = activity_track.emp_fkey) WHERE activity_track.status = 1 and activity_track_fkey = " . $_REQUEST['id'] . " ORDER BY activity_track_fkey DESC ");

            if($editData['0']['activity_track']['created_by'] != '' && $editData['0']['activity_track']['created_by'] != 'Admin') {
              $createdQuery = $this->EmployeeDetails->find('first', array("conditions" => array("emp_pkey" => $editData['0']['activity_track']['created_by'] )));
              $this->set('created_by', $createdQuery['EmployeeDetails']['first_name']);
            } else if ($editData['0']['activity_track']['created_by'] == 'Admin') {
              $this->set('created_by', 'Admin');
            }
        }
        $this->set('subtasks', $subtasks);
        $this->set('editData', $editData);

        $projects = $this->UserCredentials->query("SELECT * FROM activity_projects WHERE status = 1 ");
        $this->set('projects', $projects);

        $activity_status = $this->UserCredentials->query("SELECT * FROM activity_status WHERE status = 1 ORDER BY activity_order ");
        $this->set('activity_status', $activity_status);

        $this->set('arr_users', $this->EmployeeDetails->find('all', array("conditions" => array("status" => 1))));

        $resp_data = array();

    }

    public function save2() {

        $this->ActivityProjects->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $this->ActivityProjects->save($arr_form_data);

        echo json_encode(array('msg' => 'Data saved successfully', 'success' => 1 ));

    }

    public function save() {
        
        $this->Activity->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;

        $arr_form_data = $this->request->data;

        $arr_save_data = $arr_form_data;

        $subtask = false;
        $fkey = '';

        if(isset($arr_form_data['transfer_to']) && $arr_form_data['transfer_to'] != '') {
            $arr_save_data['emp_fkey'] = $arr_form_data['transfer_to'];
            $transfer_data = array(
                "activity_track_fkey" => $arr_form_data['activity_track_pkey'],
                "emp_fkey" => $arr_form_data['emp_fkey'],
                "project_fkey" => $arr_form_data['project_fkey'],
                "activity_date" => $arr_form_data['activity_date'],
                "activity_type" => $arr_form_data['activity_type'],
                "summary" => $arr_form_data['summary'],
                "description" => $arr_form_data['description'],
                "estimated_time" => $arr_form_data['estimated_time'],
                "status_fkey" => 3,
                "created_by" => ($this->Session->read('emp_fkey')) ? $this->Session->read('emp_fkey'): 'Admin',
                "notified_to" => $arr_form_data['notified_to']
            );
            $this->Activity->save($transfer_data);
        }

        if(isset($arr_form_data['notified_to']) && $arr_form_data['notified_to'] != '') {

            $notified_emp = $this->EmployeeDetails->find('first', array("conditions" => array("status" => 1, "emp_pkey" => $arr_form_data['notified_to'])));
            $assigned = $this->EmployeeDetails->find('first', array("conditions" => array("status" => 1, "emp_pkey" => $arr_save_data['emp_fkey'])));

            // get assigned emp's email id too
            // check if its an subtask created
            if($notified_emp['EmployeeDetails']['email'] != '' || $assigned['EmployeeDetails']['email'] != '') {

                if($arr_form_data['activity_track_pkey'] != '') {
                    $this->sendMail(
                        $notified_emp['EmployeeDetails']['email'],
                        $assigned['EmployeeDetails']['email'],
                        '<div style="color: #5c5c5c; line-height: 170%; text-align: left; word-wrap: break-word;">
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;"><strong>Hi, </strong></span></p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;">Task #'. $arr_form_data['activity_track_pkey'] .' - '. $arr_form_data['summary'] .' has been modified. &nbsp;</span></p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;">Please login to the system to know more.&nbsp;</span></p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;">With Regards,</span></p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;"><strong>Team</strong></span></p>
                        </div>',
                        'MyPayrollMaster - Activity #' . $arr_form_data['activity_track_pkey'] . ' - '. $arr_form_data['summary'] .' Has bee modified'
                    );
                } else {
                    $this->sendMail(
                        $notified_emp['EmployeeDetails']['email'],
                        $assigned['EmployeeDetails']['email'],
                        '<div style="color: #5c5c5c; line-height: 170%; text-align: left; word-wrap: break-word;">
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;"><strong>Hi, </strong></span></p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;">Task #'. $arr_form_data['activity_track_pkey'] .' - '. $arr_form_data['summary'] .' has been created. &nbsp;</span></p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 20px; line-height: 34px;"><span style="line-height: 34px; font-size: 20px;">Activity Details:</span></span></p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 14px; line-height: 23.8px;">&nbsp;<strong>Summary</strong>: '. $arr_form_data['summary'] .'</span></p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 14px; line-height: 23.8px;">&nbsp;<strong>Due Date</strong>: '. $arr_form_data['activity_date'] .'</span></p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 14px; line-height: 23.8px;">&nbsp;<strong>Estimated Time</strong>: '. $arr_form_data['estimated_time'] .'</span></p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;">Description of Task:</span></p>
                            <p style="font-size: 14px; line-height: 170%;">'. $arr_form_data['description'] .'</p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;">If you have any questions/issues regarding the process, feel free to contact us.&nbsp;</span></p>
                            <p style="font-size: 14px; line-height: 170%;">&nbsp;</p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;">With Regards,</span></p>
                            <p style="font-size: 14px; line-height: 170%;"><span style="font-size: 16px; line-height: 27.2px;"><strong>Team</strong></span></p>
                        </div>',
                        'MyPayrollMaster - Activity #' . $arr_form_data['activity_track_pkey'] . ' - ' . $arr_form_data['summary'] . ' has been created'
                    );
                }

            }
            
        }

        if($arr_form_data['activity_track_pkey'] == '') {
          $arr_save_data["created_by"] = ($this->Session->read('emp_fkey')) ? $this->Session->read('emp_fkey'): 'Admin';
        }

        unset($arr_save_data['activity_date2']);
        unset($arr_save_data['estimated_time2']);
        unset($arr_save_data['summary2']);
        unset($arr_save_data['start_time']);
        unset($arr_save_data['end_time']);
        unset($arr_save_data['duration']);

        $this->Activity->save($arr_save_data);

        if(isset($arr_form_data['start_time']) && $arr_form_data['start_time'] != '') {
            if($arr_form_data['activity_track_pkey'] == '') {
              $ins_id = $this->Activity->getLastInsertID();
            } else {
              $ins_id = $arr_form_data['activity_track_pkey'];
            }
            
            // $subtask = true;
            $transfer_data = array(
                "activity_track_pkey" => "",
                "activity_track_fkey" => $ins_id,
                "emp_fkey" => $arr_form_data['emp_fkey'],
                "project_fkey" => $arr_form_data['project_fkey'],
                "summary" => $arr_form_data['summary2'],
                "activity_date" => $arr_form_data['activity_date'],
                "activity_type" => $arr_form_data['activity_type'],
                "description" => $arr_form_data['description'],
                "estimated_time" => $arr_form_data['estimated_time'],
                "start_time" => $arr_form_data['start_time'],
                "end_time" => $arr_form_data['end_time'],
                "duration" => $arr_form_data['duration'],
                "status_fkey" => 3,
                "notified_to" => $arr_form_data['notified_to']
            );
            
            $this->Activity->save($transfer_data);
            
        } else {
            
        }

        echo json_encode(array('msg' => 'Data saved successfully', 'isSubTask' => $subtask, 'fkey' => $fkey, 'success' => 1 ));
    }

    public function sendMail($toEmail = '', $toEmail2 = '', $htmlcontent = '', $subject = '')
    {
        $this->autoRender = FALSE;
        $body = $this->getTemplate($htmlcontent);


        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));

        $mail = new PHPMailer();

        $mail->IsSMTP();
		 
        //SMTP DEBUGGING - Output connection log files
        $mail->SMTPDebug = false;
        // Debugoutput

		    $mail->SMTPAuth   = true;
        $mail->Host       = 'smtp.zoho.in';
        $mail->Port       = 587;
        $mail->Username   = 'noreply@mypayrollmaster.online';
        $mail->Password   = '@Password90#';
		
		    $mail->SetFrom("noreply@mypayrollmaster.online", 'My Payroll Master');
        $mail->AddReplyTo("noreply@mypayrollmaster.online",  'My Payroll Master');
        $mail->Subject    = $subject;
        $mail->AltBody    = '<!DOCTYPE html>';

        $mail->MsgHTML($body);

        $mail->AddAddress($toEmail, 'MyPayrollMaster User');
        $mail->AddAddress($toEmail2, 'MyPayrollMaster User');
        
        //CHECK IF WE SHOULD SEND EMAIL
        // if(!$mail->Send()) {
            //     //echo 'The mail to '.$to.' failed to send. Check your SMTP settings in config.php<br />';
            //     return "false";
            // } else {
            //     //echo 'The mail was sent successfully to '.$to.'.<br />';
            //     return "true";
            // }
    }

    function getTemplate($body = '') {
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
        <!--[if gte mso 9]>
        <xml>
          <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
          </o:OfficeDocumentSettings>
        </xml>
        <![endif]-->
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta name="x-apple-disable-message-reformatting">
          <!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]-->
          <title></title>
          
            <style type="text/css">
              table, td { color: #000000; } a { color: #01499d; text-decoration: underline; } @media (max-width: 480px) { #u_content_heading_1 .v-container-padding-padding { padding: 20px !important; } #u_content_heading_1 .v-font-size { font-size: 23px !important; } #u_content_text_2 .v-container-padding-padding { padding: 40px 30px 50px 15px !important; } #u_content_button_1 .v-size-width { width: 81% !important; } }
        @media only screen and (min-width: 620px) {
          .u-row {
            width: 600px !important;
          }
          .u-row .u-col {
            vertical-align: top;
          }
        
          .u-row .u-col-100 {
            width: 600px !important;
          }
        
        }
        
        @media (max-width: 620px) {
          .u-row-container {
            max-width: 100% !important;
            padding-left: 0px !important;
            padding-right: 0px !important;
          }
          .u-row .u-col {
            min-width: 320px !important;
            max-width: 100% !important;
            display: block !important;
          }
          .u-row {
            width: calc(100% - 40px) !important;
          }
          .u-col {
            width: 100% !important;
          }
          .u-col > div {
            margin: 0 auto;
          }
        }
        body {
          margin: 0;
          padding: 0;
        }
        
        table,
        tr,
        td {
          vertical-align: top;
          border-collapse: collapse;
        }
        
        p {
          margin: 0;
        }
        
        .ie-container table,
        .mso-container table {
          table-layout: fixed;
        }
        
        * {
          line-height: inherit;
        }
        
        </style>
          
          
        
        <!--[if !mso]><!--><link href="https://fonts.googleapis.com/css?family=Raleway:400,700&display=swap" rel="stylesheet" type="text/css"><link href="https://fonts.googleapis.com/css?family=Rubik:400,700&display=swap" rel="stylesheet" type="text/css"><!--<![endif]-->
        
        </head>
        
        <body class="clean-body u_body" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #e7e7e7;color: #000000">
          <!--[if IE]><div class="ie-container"><![endif]-->
          <!--[if mso]><div class="mso-container"><![endif]-->
          <table style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #e7e7e7;width:100%" cellpadding="0" cellspacing="0">
          <tbody>
          <tr style="vertical-align: top">
            <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top">
            <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color: #e7e7e7;"><![endif]-->
            
        
        <div class="u-row-container" style="padding: 0px;background-color: transparent">
          <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #02416b;">
            <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
              <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px;"><tr style="background-color: #02416b;"><![endif]-->
              
        <!--[if (mso)|(IE)]><td align="center" width="600" style="width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;" valign="top"><![endif]-->
        <div class="u-col u-col-100" style="max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;">
          <div style="width: 100% !important;">
          <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;"><!--<![endif]-->
          
        <table style="font-family:Rubik,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
          <tbody>
            <tr>
              <td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:Rubik,sans-serif;" align="left">
                
          <h1 class="v-font-size" style="margin: 0px; color: #ffffff; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 22px;">
            <strong>MyPayrollMaster</strong>
          </h1>
        
              </td>
            </tr>
          </tbody>
        </table>
        
          <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
          </div>
        </div>
        <!--[if (mso)|(IE)]></td><![endif]-->
              <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
            </div>
          </div>
        </div>
        
        
        
        <div class="u-row-container" style="padding: 0px;background-color: transparent">
          <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #01499d;">
            <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
              <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px;"><tr style="background-color: #01499d;"><![endif]-->
              
        <!--[if (mso)|(IE)]><td align="center" width="600" style="width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;" valign="top"><![endif]-->
        <div class="u-col u-col-100" style="max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;">
          <div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
          <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
          
        <table id="u_content_heading_1" style="font-family:Rubik,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
          <tbody>
            <tr>
              <td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:20px 25px;font-family:Rubik,sans-serif;" align="left">
                
          <h1 class="v-font-size" style="margin: 0px; color: #ffffff; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: Raleway,sans-serif; font-size: 27px;">
            New Task Created - New tjaknsks
          </h1>
        
              </td>
            </tr>
          </tbody>
        </table>
        
          <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
          </div>
        </div>
        <!--[if (mso)|(IE)]></td><![endif]-->
              <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
            </div>
          </div>
        </div>
        
        
        
        <div class="u-row-container" style="padding: 0px;background-color: transparent">
          <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #f5f5f5;">
            <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
              <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px;"><tr style="background-color: #f5f5f5;"><![endif]-->
              
        <!--[if (mso)|(IE)]><td align="center" width="598" style="width: 598px;padding: 0px;border-top: 1px solid #CCC;border-left: 1px solid #CCC;border-right: 1px solid #CCC;border-bottom: 1px solid #CCC;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;" valign="top"><![endif]-->
        <div class="u-col u-col-100" style="max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;">
          <div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
          <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 1px solid #CCC;border-left: 1px solid #CCC;border-right: 1px solid #CCC;border-bottom: 1px solid #CCC;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
          
        <table id="u_content_text_2" style="font-family:Rubik,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
          <tbody>
            <tr>
              <td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:40px 50px 50px;font-family:Rubik,sans-serif;" align="left">
                
                ' . $body . '
        
              </td>
            </tr>
          </tbody>
        </table>
        
          <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
          </div>
        </div>
        <!--[if (mso)|(IE)]></td><![endif]-->
              <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
            </div>
          </div>
        </div>
        
        
        
        <div class="u-row-container" style="padding: 0px;background-color: transparent">
          <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #01499d;">
            <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
              <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px;"><tr style="background-color: #01499d;"><![endif]-->
              
        <!--[if (mso)|(IE)]><td align="center" width="600" style="width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;" valign="top"><![endif]-->
        <div class="u-col u-col-100" style="max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;">
          <div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
          <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
          
        <table id="u_content_button_1" style="font-family:Rubik,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
          <tbody>
            <tr>
              <td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:35px 10px;font-family:Rubik,sans-serif;" align="left">
                
        <div align="center">
          <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;font-family:Rubik,sans-serif;"><tr><td style="font-family:Rubik,sans-serif;" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://login.mypayrollmaster.online" style="height:52px; v-text-anchor:middle; width:266px;" arcsize="0%" strokecolor="#ffffff" strokeweight="3px" fillcolor="#01499d"><w:anchorlock/><center style="color:#FFFFFF;font-family:Rubik,sans-serif;"><![endif]-->
            <a href="https://login.mypayrollmaster.online" target="_blank" class="v-size-width" style="box-sizing: border-box;display: inline-block;font-family:Rubik,sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #FFFFFF; background-color: #01499d; border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px; width:47%; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;border-top-color: #ffffff; border-top-style: solid; border-top-width: 3px; border-left-color: #ffffff; border-left-style: solid; border-left-width: 3px; border-right-color: #ffffff; border-right-style: solid; border-right-width: 3px; border-bottom-color: #ffffff; border-bottom-style: solid; border-bottom-width: 3px;">
              <span style="display:block;padding:16px 20px;line-height:120%;"><strong><span style="font-size: 16px; line-height: 19.2px; font-family: Raleway, sans-serif;">View Task</span></strong></span>
            </a>
          <!--[if mso]></center></v:roundrect></td></tr></table><![endif]-->
        </div>
        
              </td>
            </tr>
          </tbody>
        </table>
        
          <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
          </div>
        </div>
        <!--[if (mso)|(IE)]></td><![endif]-->
              <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
            </div>
          </div>
        </div>
        
        
        
        <div class="u-row-container" style="padding: 0px;background-color: transparent">
          <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;">
            <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
              <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px;"><tr style="background-color: #ffffff;"><![endif]-->
              
        <!--[if (mso)|(IE)]><td align="center" width="600" style="width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;" valign="top"><![endif]-->
        <div class="u-col u-col-100" style="max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;">
          <div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
          <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
          
        <table style="font-family:Rubik,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
          <tbody>
            <tr>
              <td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:0px 0px 20px;font-family:Rubik,sans-serif;" align="left">
                
          <table height="0px" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 4px solid #1a2e35;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
            <tbody>
              <tr style="vertical-align: top">
                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                  <span>&#160;</span>
                </td>
              </tr>
            </tbody>
          </table>
        
              </td>
            </tr>
          </tbody>
        </table>
        
          <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
          </div>
        </div>
              <!--[if (mso)|(IE)]></td><![endif]-->
              <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
            </div>
          </div>
        </div>
        
        
            <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
            </td>
          </tr>
          </tbody>
          </table>
          <!--[if mso]></div><![endif]-->
          <!--[if IE]></div><![endif]-->
        </body>
        
        </html>
        ';
    }

    public function listData() {


        $this->Activity->useDbConfig = $this->Session->read('ds');
        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;
        $limit = $arr_data['rows'];
        $page = $arr_data['page'];
        $offset = ($page - 1) * $limit;

        $sort = isset($arr_data['sort']) ? strval($arr_data['sort']) : 'Activity.status_fkey';
        
        $order = isset($arr_data['order']) ? strval($arr_data['order']) : 'ASC';

        $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '';

        $where = array('Activity.status = 1 and activity_track_fkey is null');
        if ($user_fkey == '') {
            $where[] = 'EmployeeDetails.status = 1 ';
        } else {
            $where[] = 'EmployeeDetails.status = 1 and `Activity`.`emp_fkey` = ' . $user_fkey;
        }

        if(isset($arr_data['activitystatus']) && $arr_data['activitystatus'] != '') {
            $where[] = 'Activity.status_fkey =  ' . $arr_data['activitystatus'];
        }

        if(isset($arr_data['project']) && $arr_data['project'] != '') {
            $where[] = 'Activity.project_fkey =  ' . $arr_data['project'];
        }

        if(isset($arr_data['employee']) && $arr_data['employee'] != '') {
            $where[] = 'Activity.emp_fkey =  ' . $arr_data['employee'];
        }

        if(isset($arr_data['due_date']) && $arr_data['due_date'] != '') {
            $where[] = "Activity.activity_date =  '" . $arr_data['due_date'] . "' ";
        }

        if(isset($arr_data['project_id']) && $arr_data['project_id'] != '') {
            $where[] = "Activity.activity_track_pkey =  '" . $arr_data['project_id'] . "' ";
        }

        if(isset($arr_data['emp'])){
            $where[] = "(EmployeeDetails.first_name like '%".$arr_data['emp']."%' OR Projects.activity_projects_head like '%".$arr_data['emp']."%'  OR EmployeeDetails.last_name like '%".$arr_data['emp']."%')";
        }

        if($this->Session->read('emp_fkey')){
            $where[] = 'EmployeeDetails.status = 1 and (`Activity`.`emp_fkey` = ' . $this->Session->read('emp_fkey') . ' OR `Activity`.`created_by` = ' . $this->Session->read('emp_fkey') . ' )';
        }
      
        $totalcount = $this->Activity->find("count",
            array('joins' => array(
                array('table' => 'emp_details',
                    'alias' => 'EmployeeDetails',
                    'type' => 'left',
                    'conditions' => array('EmployeeDetails.emp_pkey = Activity.emp_fkey')
                ),
                array('table' => 'activity_projects',
                'alias' => 'Projects',
                'type' => 'left',
                'conditions' => array('Projects.activity_projects_pkey = Activity.project_fkey')
            )),
            'conditions' => $where
        ));
        
        $arr_menu = $this->Activity->find('all', array('joins' => array(
            array('table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'left',
                'conditions' => array('EmployeeDetails.emp_pkey = Activity.emp_fkey')
            ),
            array('table' => 'activity_projects',
                'alias' => 'Projects',
                'type' => 'left',
                'conditions' => array('Projects.activity_projects_pkey = Activity.project_fkey')
            ),
            array('table' => 'activity_status',
                'alias' => 'ActivityStatus',
                'type' => 'left',
                'conditions' => array('ActivityStatus.activity_status_pkey = Activity.status_fkey')
            )),
            'fields' => array('EmployeeDetails.first_name', 'ROUND((SELECT SUM(duration) FROM activity_track WHERE status = 1 and activity_track_fkey = Activity.activity_track_pkey),2) as totald', 'EmployeeDetails.last_name','EmployeeDetails.mobile_no', 'Activity.*','Projects.activity_projects_head', 'ActivityStatus.activity_status'),
            'order'=>array($sort=>$order),
            'conditions' => $where,
            'offset'=>$offset,
            'limit' => intval($limit))
        );

        $rows = array();

        foreach ($arr_menu as $key => $val) {

            $val['EmployeeDetails']['fullname'] = $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name'];
            $val['Activity']['activity_time'] = $val['Activity']['start_time'] . ' ' . $val['Activity']['end_time'];
            $val['Projects']['activity_projects_head'] = ($val['Projects']['activity_projects_head'] != '') ? $val['Projects']['activity_projects_head'] : 'No Project Chosen';

            $val['Projects']['activity_track_pkey'] = $val['Activity']['activity_track_pkey']; // . ($val['Activity']['activity_track_fkey'] ? '/' . $val['Activity']['activity_track_fkey'] : '/' . $val['Activity']['activity_track_pkey']);

            switch($val['ActivityStatus']['activity_status']) {
              case 'Under Review': $val['ActivityStatus']['activity_status'] = "<span style='background: orange; color: #fff; padding: 12px; '>". $val['ActivityStatus']['activity_status'] . "</span>"; break;
              case 'In Progress': $val['ActivityStatus']['activity_status'] = "<span style='background: blue; color: #fff; padding: 12px; '>". $val['ActivityStatus']['activity_status'] . "</span>"; break;
              case 'To Do': $val['ActivityStatus']['activity_status'] = "<span style='background: pink; color: #fff; padding: 12px; '>". $val['ActivityStatus']['activity_status'] . "</span>"; break;
              case 'Completed': $val['ActivityStatus']['activity_status'] = "<span style='background: green; color: #fff; padding: 12px; '>". $val['ActivityStatus']['activity_status'] . "</span>"; break;
              default : $val['ActivityStatus']['activity_status'] = "<span style='background: green; color: #fff; padding: 12px; '>". $val['ActivityStatus']['activity_status'] . "</span>"; break;
            }

            $rows[] = array_merge($val['Activity'], $val['EmployeeDetails'], $val['Projects'], $val['ActivityStatus'], $val['0']);
        }

        $resp_data["total"] = $totalcount;
        $resp_data["rows"] = $rows;

        echo json_encode($resp_data);
    }

    public function listProjectsData() {


      $this->ActivityProjects->useDbConfig = $this->Session->read('ds');
      $resp_data = array();

      $this->autoRender = false;
      $arr_data = $this->request->data;
      $limit = $arr_data['rows'];
      $page = $arr_data['page'];
      $offset = ($page - 1) * $limit;

      $sort = isset($arr_data['sort']) ? strval($arr_data['sort']) : 'activity_projects_pkey';
      
      $order = isset($arr_data['order']) ? strval($arr_data['order']) : 'DESC';

      $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '';

      $where = array('ActivityProjects.status = 1');
    
      $totalcount = $this->ActivityProjects->find("count", array( 'conditions' => $where ));
      
      $arr_menu = $this->ActivityProjects->find('all', array(
          'fields' => array('ActivityProjects.*'), 
          'order'=>array($sort=>$order),
          'conditions' => $where,
          'offset'=>$offset,
          'limit' => intval($limit))
      );

      $rows = array();

      foreach ($arr_menu as $key => $val) {
          $rows[] = array_merge($val['ActivityProjects']);
      }

      $resp_data["total"] = $totalcount;
      $resp_data["rows"] = $rows;

      echo json_encode($resp_data);
  }

    //delete
    public function deleteRow() {
        $this->autoRender = FALSE;
        $this->Activity->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //debug($ar_ids);
            $this->Activity->updateAll(
                    array('status' => 0), array('activity_track_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s) deleted successfully.";
        }
        echo json_encode($result);
    }
    
    public function deleteProject() {
      $this->autoRender = FALSE;
      $this->ActivityProjects->useDbConfig = $this->Session->read('ds');
      $result = array('success' => 0);
      if (isset($_REQUEST["ids"])) {
          $ar_ids = explode(",", $_REQUEST["ids"]);
          //debug($ar_ids);
          $this->ActivityProjects->updateAll(
                  array('status' => 0), array('activity_projects_pkey' => $ar_ids));
          $result['success'] = 1;
          $result['msg'] = "Record(s) deleted successfully.";
      }
      echo json_encode($result);
    }

    public function deleteLogTime() {
      $this->autoRender = FALSE;
      $this->Activity->useDbConfig = $this->Session->read('ds');
      $result = array('success' => 0);
      if (isset($_REQUEST["id"])) {
          $ar_ids = $_REQUEST["id"];
          //debug($ar_ids);
          $this->Activity->updateAll(
                  array('status' => 0), array('activity_track_pkey' => $ar_ids));
          $result['success'] = 1;
          $result['msg'] = "Record(s) deleted successfully.";
      }
      echo json_encode($result);
    }

}