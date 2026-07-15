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
ini_set('max_execution_time', 300);
App::uses('ConnectionManager', 'Cake\Datasource');
App::uses('CakeEmail', 'Network/Email');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
ini_set("display_errors", 1);
class YearEndController extends AppController {

	/**
	 * Controller name
	 *
	 * @var string
	 */
	public $name = 'YearEnd';
	/**
	 * This controller does not use a model
	 *
	 * @var array
	 */
	public $uses = array('UserCredentials', 'CentralControl', 'CentralUserCredentials', 'Registrations', 'Currency', 'Country', 'MasterDb','EmployeeMenu','Units','EmployeeDetails');

	public $components = array('LoginManagement', 'Session','Email','MasterdataManagement');


    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $plan=$this->EmployeeDetails->query('SELECT plan FROM comp_contact_info');
        $plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
		 $this->set('plan',$plan);

        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();

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
                $arr_branches = array_values(array_filter($arr_branches, function ($branch) use ($is_ho) {
                    return $branch['branch_code'] === $is_ho;
                }));
            }
        }
        // End

        $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 ");
        $finyears = isset($currentyear['0']) ? $currentyear['0']['fin_year']['fin_year'] : null;
        $this->set('year', $finyears);
        $this->set('branches', $arr_branches);
        //                debug($arr_branches);

    }
       function loadprocess(){
           $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $this->Units->useDbConfig = $this->Session->read('ds');
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;
            $branch = $arr_form_data['id'];
            $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code = '$branch' ");
            $finyears = isset($currentyear['0'])?$currentyear['0']['fin_year']['fin_year']:null;
            $this->set('year',$finyears);
            $start_month = isset($currentyear['0']['fin_year']['start_month'])?$currentyear['0']['fin_year']['start_month']:0;
            $end_month = isset($currentyear['0']['fin_year']['end_month'])?$currentyear['0']['fin_year']['end_month']:0;
            if($finyears == null){
            die("Selected Branch Have No Active Fin year");
            }
            $leavepolicy = $this->Units->query("select * from leavepolicy_group where status = 1");
            $arr_leaves = array();
            
            foreach ($leavepolicy as $leavegroups){
                $leavepolicy_group_id = $leavegroups['leavepolicy_group']['LEAVEPOLICY_GROUP_ID'];
                $leavessall = $this->Units->query("select leavepolicy.*,salary_head_items.* from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) where leavepolicy.status = 1 and salary_head_items.status = '1' and LEAVEPOLICY_GROUP_ID = '$leavepolicy_group_id' ");
                //debug($leavessall);
                $leavesss = array();
                foreach($leavessall as $l){
                    $salary_head_items = $l['salary_head_items']['salary_head_item_pkey'];
                    $pending_req = $this->Units->query("select count(LEAVEENTRYID) as cont from leaveentries where salary_head_item_fkey in ('$salary_head_items') and LEAVESTATUS in ('Applied','Authorized') and FROMDATE between '$start_month' and '$end_month' and  EMP_fkey in (select emp_pkey from emp_details where branch_code='$branch') ");
                    $leavess = $pending_req['0']['0']['cont'];
                    
                    $leavesss[] = array(
                        'leav'=>$l,
                            'pendings'=>$leavess);
                //    debug($leavesss);
                }
                $arr_leaves[] = array(
                    'leavegroup'=>$leavegroups,
                    'leavepolicy'=>$leavesss
                );
            }
            //debug($arr_leaves);
            $this->set('leaves',$arr_leaves);
       }
       public function loaders(){
           $this->Units->useDbConfig = $this->Session->read('ds');
           
       }
       public function approve(){
           $this->autoRender = FALSE;
           $this->Units->useDbConfig = $this->Session->read('ds');
           $arr_request_data = $this->request->data;
           $branch = $arr_request_data['branch'];
           $today = date('Y-m-d');
           $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code = '$branch' ");
           if($currentyear){
           $start_month = $currentyear['0']['fin_year']['start_month'];
           $end_month = $currentyear['0']['fin_year']['end_month'];
           $auth_leaves = $this->Units->query("update leaveentries set LEAVESTATUS = 'Approved',ISAutherized = '1',ISAutherizedby = '0',Autherized_date = '$today',ISAPPROVED = '1',APPROVEDBY = '0',APPROVED_date = '$today',Reason = 'Auto Appproval',REMARKS = 'Auto Approve' where LEAVESTATUS in ('Applied') and FROMDATE between '$start_month' and '$end_month'");

           $approve_leaves = $this->Units->query("update leaveentries set LEAVESTATUS = 'Approved',ISAutherized = '1',ISAutherizedby = '0',Autherized_date = '$today',ISAPPROVED = '1',APPROVEDBY = '0',APPROVED_date = '$today',Reason = 'Auto Appproval',REMARKS = 'Auto Approve' where LEAVESTATUS in ('Authorized') and FROMDATE between '$start_month' and '$end_month'");
           return 1;
           }
       }
       public function processleave(){
           $this->autoRender = FALSE;
           $this->Units->useDbConfig = $this->Session->read('ds');
           $date = date("Y");
           $arr_request_data = $this->request->data;
           $branch = $arr_request_data['branch'];
           $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code = '$branch' ");
          // debug($currentyear);
           if($currentyear){
                $date = date("Y",strtotime($currentyear['0']['fin_year']['start_month']));
           }
           $arr_request_data = $this->request->data;
           
           
           try {
               $currentyear = $this->Units->query(" select year_ending_fn('$date','$branch') ");
              // debug($currentyear);
           } catch (Exception $ex) {
               echo json_encode(array("success"=>FALSE,"message"=>"Proccessing failed"));
           }
           echo json_encode(array("success"=>TRUE,"message"=>"Proccessed Successfully"));
       }

       public function notice(){
           $this->autoRender = FALSE;
           $this->Units->useDbConfig = $this->Session->read('ds');
           $arr_request_data = $this->request->data;
           $branch = $arr_request_data['branch'];
           $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code = '$branch' ");
           if($currentyear){
           $start_month = $currentyear['0']['fin_year']['start_month'];
           $end_month = $currentyear['0']['fin_year']['end_month'];
           $salary_head_items_fkey = $arr_request_data['salary_head_items'];
           $this->Units->useDbConfig = $this->Session->read('ds');
           $auth_mails = $this->Units->query("select distinct(email) from emp_details where emp_pkey in (select ISAutherizedby from leaveentries where LEAVESTATUS in ('Applied') and salary_head_item_fkey = '$salary_head_items_fkey' and FROMDATE between '$start_month' and '$end_month')");
           $approved_mails = $this->Units->query("select distinct(email) from emp_details where emp_pkey in (select ISAutherizedby from leaveentries where LEAVESTATUS in ('Authorized') and salary_head_item_fkey = '$salary_head_items_fkey' and FROMDATE between '$start_month' and '$end_month')");
           $allmails = array_merge($auth_mails,$approved_mails);
           $arr_mails = array();
           foreach ($allmails as $mails){
               $arr_mails[] = $mails['emp_details']['email'];
           }
           $tags = implode("','" , $arr_mails);
           //debug($tags);
           try {
                App::import('Vendor', 'PHPMailer', array('file'=>'PHPMailer/PHPMailerAutoload.php'));
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
                foreach ($allmails as $mails){
                        $mail->addAddress($mails['emp_details']['email']);     // Add a recipient
                }
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->AddAttachment('<?php echo $this->webroot; ?>');
                $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
                $mail->Subject = "MyPayrollMaster - Reminder Mail To Action Your Pending Requests";
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
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="Payroll" ></a></td>
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
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Welcome to <font style="color:#fff;"> MyPayrollMaster</font> </h1>
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
                                        	<td>
                                            	<h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">This is an informative mail to you to please take appropriate actions on employees leave requests from your account on mypayrollmaster.com .You have pending requests from your subordinates, please take actions immediately to process the year end. </div>
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

                if(!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                echo 'Message has been sent';
            }
            } catch (Exception $ex) {
                debug($ex);
            }
           }
           
       }
            

}
