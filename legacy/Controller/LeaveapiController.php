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
ini_set("display_errors", 1);

App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class LeaveapiController extends Controller
{

    /**
     * Controller name
     *
     * @var string
     */
    // public $layout = "default";
    public $name = 'Leaveapi';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel', 'LeaveRequests', 'EmpLeaveApproval', 'EmployeeConfig', 'SalaryHeadItems', 'EmployeeDetails', 'EmployeeLeaveTransaction', 'LeavePolicy', 'EmployeeInfo');
    public $components = array('LoginManagement', 'Session', 'Email');

    /*
     * Leave List Landing Page
     */

    function postCurlRequest($url, $post_array, $check_ssl = true)
    {
        $cmd = "curl -L -X POST -H 'Content-Type: application/json'";
        $cmd .= " -d '" . json_encode($post_array) . "' '" . $url . "'";

        if (!$check_ssl) {
            $cmd .= "'  --insecure"; // this can speed things up, though it's not secure
        }
        $cmd .= " > /dev/null 2>&1 &"; // don't wait for response

        // echo $cmd;die;

        exec($cmd, $output, $exit);
        return $exit == 0;
    }

    public function checkmails()
    {
        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $data['auth_name'] = "SANJUN DEV";
        $data['msgs'] = 'Test';
        $data['applied_emp'] = "sanjun";
        $data['applied_emp_id'] = 12611;
        $data['leavetype'] = "Casual Leave";
        $data['leavedays'] = 1;
        $data['leavefrom'] = '2023-06-02';
        $data['leaveend'] = '2023-06-02';;
        $data['applied'] = 11;
        $data['reason'] = "test";
        $data['duties'] = "2313434243";

        $url = 'https://v1.mypayrollmaster.online/Leaveapi/sendauthorizationmail';

        $response = $this->postCurlRequest($url, $data);

        var_dump($response);

        echo "Hello";
    }

    public function checkLogin()
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $data = $_GET;

        $controldb_config = ConnectionManager::getDataSource('controldb')->config;
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db($controldb_config['database'], $link);
        $res = mysql_query("SELECT `single_signon_fn`('httaiosdiosd', 'DEMO', '12611', 'projects@greatleap.tech', 'DEMO16') as authorize;");
        $row = mysql_fetch_assoc($res);

        if ($row['authorize'] != '0') {
            $database = explode("|", $row['authorize'])[0];

            $linkDatabase = mysql_connect('localhost', 'root', 'Localhost&*()');
            mysql_select_db($database, $linkDatabase);
            $res2 = mysql_query("SELECT user_id,password FROM mob_user_credentials WHERE user_id = 'DEMO12611' ") or die("Invalid query: " . mysql_error());
            $row2 = mysql_fetch_assoc($res2);

            header("Location: https://login.mypayrollmaster.online/Site/login?user_id=" . $row2['user_id'] . "&password=" . $row2['password']);
        } else {
            die("Invalid credentials");
        }
    }

    function sendOTP()
    {

        App::import('Vendor', 'FirebaseNotification', array('file' => 'FirebaseNotification.php'));

        $data = json_decode(file_get_contents('php://input'), true);

        $message = $data['message'];
        $messageTitle = $data['messageTitle'];

        $FCM = new FirebaseNotification();

        $userData = $this->getUID($data['attr2'], $data['email']);

        if (json_decode($userData)->status == 200) {

            if (json_decode($userData)->uIPushNotificationKey) {

                $FCM->heading = $messageTitle;
                $FCM->body = $message;

                $FCM->sendFCM(json_decode($userData)->uIPushNotificationKey);
            }
        }
    }

    public function getUID($profileID, $email)
    {
        $this->autoRender = false;
        $curl = curl_init();

        $payloadata = array(
            "profileId" => $profileID,
            "email" => $email //"theinteractivecode@gmail.com"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online//thirdpartyapi/userDetails/pushNotificationKeyVerify', //FT114
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payloadata),
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function sendauthorizationmail()
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        // $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $user_name = $this->EmployeeDetails->query("select first_name,last_name from emp_details where emp_pkey = 21 ");

        $data = json_decode(file_get_contents('php://input'), true);

        // $controldb_config = ConnectionManager::getDataSource('controldb')->config;
        // $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        // mysql_select_db($controldb_config['database'], $link);
        // $res = mysql_query('SELECT * FROM central_control WHERE control_pkey = 16');
        // $row = mysql_fetch_assoc($res);

        // if (!empty($row)) {
        //     $dbUser = $row['Admin_name'];
        //     $dbName = $row['user_db'];
        //     $config = array();

        //     // Set correct database name
        //     // Add new config to registry
        //     //ConnectionManager::create('companydb', $config);
        //     $controldb = array(
        //         'datasource' => 'Database/Mysql',
        //         'persistent' => false,
        //         'host' => 'localhost',
        //         'login' => 'root',
        //         'password' => 'Localhost&*()',
        //         'database' => $dbName,
        //         'prefix' => '',
        //         //'encoding' => 'utf8',
        //     );
        //     ConnectionManager::create('companydb', $controldb);
        //     $this->Session->write("ds", 'companydb');

        //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //     $user_name = $this->EmployeeDetails->query("select first_name,last_name from emp_details where emp_pkey = 21 ");
        //     // debug($user_name);

        // }

        try {

            $auth_name = $data['auth_name'];
            $msgs = $data['msgs'];
            $applied_emp = $data['applied_emp'];
            $applied_emp_id = $data['applied_emp_id'];
            $leavetype = $data['leavetype'];
            $leavedays = $data['leavedays'];
            $leavefrom = $data['leavefrom'];
            $leaveend = $data['leaveend'];
            $applied = $data['applied'];
            $reason = $data['reason'];
            $duties = $data['duties'];

            $actionType = $data['approvalType'];
            $email = $data['email'];

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

            if ($actionType == 'Approve' || $actionType == 'Authorize' ||  $actionType == 'cancellationOfApproval' || $actionType == 'cancellationOfAuthorization' || $actionType == 'rej') {

                if ($actionType == 'Approve') {
                    $mshbody = 'Leave Request has been Approved';
                } else if ($actionType == 'Authorize') {
                    $mshbody = 'Leave Request has been Authorized';
                } else if ($actionType == 'cancellationOfApproval') {
                    $mshbody = 'Leave Request Cancellation Has been Approved';
                } else if ($actionType == 'cancellationOfAuthorization') {
                    $mshbody = 'Leave Request Cancellation Has been Authorization';
                } else if ($actionType == 'rej') {
                    $mshbody = 'Leave Request has been rejected';
                }

                $mail->Subject = 'MPM - ' . $mshbody . "";
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
                                                                    <div style="color:#404040;font-size:15px;line-height:22px;font-weight:lighter;padding:0;margin:0">Hi ' . $applied_emp . ',
                                                                    <br>
                                                                    You have new leave request to take action.     
                                                                    <br><br>' . $mshbody . '. </div>
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
            } else {
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
                                                                    <div style="color:#404040;font-size:15px;line-height:22px;font-weight:lighter;padding:0;margin:0">Hi ' . $auth_name . ',
                                                                    <br>
                                                                    You have new leave request to take action.     
                                                                    <br><br>' . $msgs . ' </div>
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
            }



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

    public function getEmployeesCount()
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $controldb_config = ConnectionManager::getDataSource('controldb')->config;
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db($controldb_config['database'], $link);
        $res = mysql_query('SELECT * FROM central_control WHERE control_pkey = 16');
        $row = mysql_fetch_assoc($res);

        if (!empty($row)) {
            $dbUser = $row['Admin_name'];
            $dbName = $row['user_db'];
            $config = array();

            // Set correct database name
            // Add new config to registry
            //ConnectionManager::create('companydb', $config);
            $controldb = array(
                'datasource' => 'Database/Mysql',
                'persistent' => false,
                'host' => 'localhost',
                'login' => 'root',
                'password' => 'Localhost&*()',
                'database' => $dbName,
                'prefix' => '',
                //'encoding' => 'utf8',
            );
            ConnectionManager::create('companydb', $controldb);
            $this->Session->write("ds", 'companydb');

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $user_name = $this->EmployeeDetails->query("select first_name,last_name from emp_details where emp_pkey = 21 ");
            // debug($user_name);

        }

        var_dump("hello");
    }
}
