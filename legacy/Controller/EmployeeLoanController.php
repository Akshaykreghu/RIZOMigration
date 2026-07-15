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
class EmployeeLoanController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EmployeeLoan';
    public $datatable;
    public $san = 'SANJUNDEV';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeLoanInfo', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'LoanEmi', 'EmployeeCTC', 'EmployeeLoan', 'SalarySlip');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index() {

        $emp_pkey = $this->Session->read('emp_fkey');
        $this->set("emp_pkey", $emp_pkey);
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set("arr_loan_master", $arr_loan_master);
    }

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */

    /*
     * Show tax Head Details form
     */

    public function getcontactsbysite($skey = 0) {
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $this->autoRender = false;
        if ($skey == '') {
            $where = '';
        } else {
            $where = "where branch_code = '$skey' ";
        }
        $str_contact_options_html = '';
        $arr_contacts = $this->EmployeeProfessionalDetails->query("select * from emp_details $where ");
        echo $str_contact_options_html .= '<option value="' . '' . '">' . ' All' . '</option>';
        foreach ($arr_contacts as $data) {
            $cid = $data['emp_details']['emp_pkey'];
            $name = $data['emp_details']['first_name'];
            $str_contact_options_html = '';
            $contactid = $cid;
            $contactname = $name;

            $str_contact_options_html .= '<option value="' . $contactid . '">' . $contactname . '</option>';

            echo $str_contact_options_html;
        }
    }

    public function emptaxationdetails($emp_pkey = 0) {
        
    }

    /*
     * Employee CTC Upload form
     * By santhosh on 24 Oct 2015
     */

    public function uploadandsaveempctc($ctcuploadtype = 0) {

        $this->autoRender = FALSE;
        if ($ctcuploadtype != 0) {
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_emploan_' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
//            debug($targetpath);
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $month_issues=0;
                $payroll_issues=0;
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee Name', 'Employee ID');

                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
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
                        //Iam here now
//                          debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
                        $this->SalarySlip->useDbConfig = $this->Session->read('ds');


                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeLoan');
                        foreach ($arrayempdata as $key => $row) {

                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($user_id == '') {
                                continue;
                            }

                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            ));
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_loan_pkey'] = 0;
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
//                            $arr_empctc_data['created_date'] = date('Y-m-d');
                            // $arr_empctc_data['remarks'] = 'Remark';

                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
                                $fieldValue = $row[$fieldlabel];
                                $arr_empctc_data[$field] = $fieldValue;
                            }
//                            debug($arr_empctc_data);
                            try {

                                $emi = 0;
                                $r = 0;
                                $amount = $arr_empctc_data['loan_amount'];
                                $month = $arr_empctc_data['tenure'];
                                if($month==0){
                                    continue;
                                }
//                                debug($amount);
//                                debug($month);
                                $intrest = isset($arr_empctc_data['intrest_rate'])?$arr_empctc_data['intrest_rate']:'0';
                                // debug($interest);

                                if ($intrest != '0') {
                                    //  echo ('hi');
                                    if (isset($amount) && !empty($amount))
                                        $P = $amount;
                                    if (isset($intrest) && !empty($intrest))
                                        $r = floatval($intrest / 100);
                                    if (isset($month) && !empty($month))
                                        $n = $month;
                                    if ($P != 0 && $n != 0 && $r != 0)
                                        $emi = floor(($P * $r / 12) * (pow((1 + $r / 12), $n)) / (pow((1 + $r / 12), $n) - 1));
                                    //  debug($emi);
                                }else {
                                    $emi = $amount / $month;
                                    // debug($emi);
                                    // die();
                                }

                                $date = $arr_empctc_data['emi_start_month'];
                                $this_month=date('Y-m');
                                //This is to check wether the start month is less than current month. By ****ARUL P DAS
                                if($date<$this_month){
                                    $month_issues=1;
                                    continue;
                                }
                                $arr_salary_month_check = $this->EmployeeLoan->query("select emp_salary_slip.salary_amount FROM emp_salary_slip WHERE month_year= '$date' AND emp_fkey= '$emp_fkey' and end_date_effective IS NULL ");
//                                debug($date);
                                if (count($arr_salary_month_check)>0) {//This is to check emi_start month is already processed payroll or not. By **ARUL P DAS
                                    $payroll_issues=1;
                                    continue;
                                }
                                // debug($date);
                                $month = $arr_empctc_data['tenure'];
                                if ($date) {
                                    $dates = explode("-", $date);
                                    $year = ($dates[0]);
                                    $mon = ($dates[1]);
                                    $totalmonth = ($month) + ($mon);
                                    // debug($mon);
                                }
//                                debug($totalmonth);
                                if ($totalmonth > 12) {
                                    $newyaer = round($totalmonth) / 12;
                                    $newmonth = floor($month) % 12;
                                    $finalmonth1 = ($newmonth) + ($mon);
                                    $finalyear = round($year) + round($newyaer);
                                    $finalmonth2 = floatval($finalmonth1 - 1);
                                    if ($finalmonth2 > 12) {
                                        $finalmonth = ($finalmonth2 - 12);
                                    } else {
                                        $finalmonth = $finalmonth2;
                                    }
                                } else {
                                    $finalmonth = ($totalmonth - 1);
                                    $finalyear = $year;
                                }
                                if ($finalmonth == 1) {
                                    $finalmonth = 'Jun';
                                } else if ($finalmonth == 2) {
                                    $finalmonth = 'Feb';
                                } else if ($finalmonth == 3) {
                                    $finalmonth = 'Mar';
                                } else if ($finalmonth == 4) {
                                    $finalmonth = 'Apr';
                                } else if ($finalmonth == 5) {
                                    $finalmonth = 'May';
                                } else if ($finalmonth == 6) {
                                    $finalmonth = 'Jun';
                                } else if ($finalmonth == 7) {
                                    $finalmonth = 'Jul';
                                } else if ($finalmonth == 8) {
                                    $finalmonth = 'Aug';
                                } else if ($finalmonth == 9) {
                                    $finalmonth = 'Sep';
                                } else if ($finalmonth == 10) {
                                    $finalmonth = 'Oct';
                                } else if ($finalmonth == 11) {
                                    $finalmonth = 'Nov';
                                } else if ($finalmonth == 12) {
                                    $finalmonth = 'Dec';
                                }

                                $form_month = $arr_empctc_data['emi_start_month'];
                                // debug($form_month);
                                if ($form_month) {
                                    $month = explode("-", $form_month);
                                    $year = $month[0];
                                    $mon = $month[1];
                                    $set_month = $year . '-' . $mon;
                                }
//                                 debug($set_month);
                                $empfkey = $arr_empctc_data['emp_fkey'];
//                                 debug($emp_fkey);
//                                debug($arr_empctc_data);
//                                $arr_salary = $this->SalarySlip->query("select emp_salary_slip.salary_amount FROM   emp_salary_slip WHERE month_year= '$set_month' AND emp_fkey= '$empfkey' ");
//                                debug($arr_salary);
//                                debug($arr_empctc_data);
//                                if (empty($arr_salary)) {
                                    $getmonth = $finalmonth . "-" . $finalyear;
                                    $arr_empctc_data['emi_end_month'] = $getmonth;
                                    $arr_empctc_data['emi_amount'] = $emi;
//                                    debug($emi);
//                                     debug($arr_empctc_data);
                                    $result1 = $this->EmployeeLoan->save($arr_empctc_data);
//                                }

                                ///The below code is to insert data into emp_loan_info table. by ***ARUL P DAS on 18/11/2019
                                $count = $arr_empctc_data['tenure'];
                                $closing_balance = $arr_empctc_data['loan_amount'];
                                $interest = isset($arr_empctc_data['intrest_rate'])?$arr_empctc_data['intrest_rate']:'0';
                                $next = strtotime($arr_empctc_data['emi_start_month']);
                                $loan_pkey = $this->EmployeeLoan->getLastInsertId();
                                $created_by=$this->Session->read('login_user_id');
                                $emp_pkey = $arr_empctc_data['emp_fkey'];
                                $arr_loan_data = array();
                                for($i = 0; $i < $count; $i++){
                                    $opening_balance = $closing_balance;
                                    $interests = $opening_balance * $interest / 100;
                                    $interestpaid = $interests * 1 / 12;
                                    $principal = $emi - $interestpaid;
                                    $closing_balance = $opening_balance - $principal;

                                    $arr_loan_data['opening_balance'] = $opening_balance;
                                    $arr_loan_data['closing_balance'] = $closing_balance;
                                    $arr_loan_data['principle'] = $principal;
                                    $arr_loan_data['interest'] = $interestpaid;
                                    $arr_loan_data['amount_to_paid'] = $emi;
                                    $arr_loan_data['loan_emi'] = $emi;
                                    $remarks="EMI for the month Rs.".round($emi);//The remark is by ARUL P DAS on 13/11/2019
                                    $month = date("Y-m", $next);
                        $result = $this->EmployeeLoanInfo->query("insert into emp_loan_info(opening_balance,emp_fkey,loan_pkey,loan_month,closing_balance,principle,interest,amount_to_paid,loan_emi,created_by,remarks)"
                    . "values('$opening_balance','$emp_pkey','$loan_pkey','$month', '$closing_balance','$principal','$interestpaid','$emi','$emi','$created_by','$remarks')");
                                    $starts = date("Y-m", strtotime("+1 month", $next));
                                    $next = strtotime($starts);
//                                     debug($arr_loan_data);
//                                     debug($emp_pkey);
//                                     debug($loan_pkey);
//                                     debug($month);
//                                     debug($created_by);
//                                     debug($remarks);

                                }
                            } catch (Exception $e) {
                                //debug($e);
                            }
                        }
                    }
                    unlink($targetpath);
                    if($month_issues==1){//This is to check loan emi start from before current month. By ***ARUL P DAS
                        $this_month=date('Y-m');
                        $msg="Loan can't be create before ".$this_month;
//                        echo json_encode(array('success' => 0, 'msg' => 'EMI start month should be greater than or equal to current month','error'));
                        echo json_encode(array('success' => 0, 'msg' => $msg,'error'));
                        exit;
                    }
                    if($payroll_issues==1){//This is to check loan emi start month is processed payroll already or not. By ***ARUL P DAS
                        $this_month=date('Y-m');
                        $msg="Loan already processed in ".$this_month;
                        echo json_encode(array('success' => 0, 'msg' => $msg,'error'));
                        exit;
                    }
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Loan imported successfully '));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Loan reviced successfully  '));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee loan import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee laon revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee loan import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee loan revision failed!'));
                    exit;
                }
            }
        }

//        else {
//            if ($ctcuploadtype == 1) {
//                //Sorry, employee loan revision failed!
//                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee loan revision failed'));
//            } else {
//                //Sorry, employee loan revision failed!
//                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee loan revision failed'));
//                exit;
//            }
//            exit;
//        }
    }

//popup for save and update
    public function form() {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        // $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        //Here added emp_company_id with employee list. By ***ARUL P DAS
        $arr_employees=$this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_details.emp_pkey not in (SELECT emp_fkey FROM `termination`  where termination.status=1) group by emp_pkey order by first_name ASC');//Here resigned employees avoided from employee list. BY ***ARUL P DAS
        $this->set("arr_employees", $arr_employees);
        //debug($arr_employees);
        $data['emp_loan_pkey'] = 0;
        $data['loan_amount'] = "";
        $data['emp_fkey'] = "";
        $data['tenure'] = 0;
        $data['intrest_rate'] = 0;
        $data['emi_amount'] = 0;
        $data['emi_start_month'] = "";
        $data['emi_end_month'] = "";
        $data['is_completed'] = "";
        $data['remarks'] = "";
        if (isset($_REQUEST['emp_loan_pkey']) && $_REQUEST['emp_loan_pkey'] != 0) {
            $data_db = $this->EmployeeLoan->find("first", array("conditions" => array(
                    "emp_loan_pkey" => $_REQUEST['emp_loan_pkey']),
            ));
            $data = $data_db['EmployeeLoan'];
            $result = array();
            $current = date("Y/m/d");
            $datechec = (strtotime($data['created_date']));
            $date = date('Y/m/d', $datechec);
            $newdate = strtotime('+3 day', strtotime($date));
            $sumdate = date('Y/m/d', $newdate);
            if ($sumdate < $current) {
                $result['success'] = 1;
                $result['msg'] = "Record(s)   successfully.";
                //echo json_encode($result);
                $this->set("data3", '');
                $this->set("data", $data);
                $this->set("data2", $result);
                //$this->set("data2", $data2);
            } else {
                $current = date("d", strtotime(date("Y/m/d")));
                $datechec = date("d", strtotime($data['created_date']));
                $lol = $current - $datechec;
                // debug($lol);
                $result['success'] = $lol;
                //debug($lol);
                //$fk = date('Y/m/d', $lol);
                //debug($fk);

                $this->set("data2", '');
                $this->set("data3", $lol);
                $this->set("data", $data, "hi", $lol);
            }
        } else {
            $this->set("data3", '');
            $this->set("data2", '');
            $this->set("data", $data);
        }

        //debug($data);
    }
    
    public function emi_upload(){
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        // $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array(
        //         'status' => 1
        // )));

        //***emp_pkey replaced with emp_company_id in the employee list. By ARUL P DAS on 05-11-2019***
        $arr_employees=$this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on(emp_details.emp_pkey=emp_proff.emp_fkey) where status=1');
        $this->set("arr_employees", $arr_employees);
        //debug($arr_employees);
        $data['emp_loan_pkey'] = 0;
        $data['loan_amount'] = "";
        $data['emp_fkey'] = "";
        $data['tenure'] = 0;
        $data['intrest_rate'] = 0;
        $data['emi_amount'] = 0;
        $data['emi_start_month'] = "";
        $data['emi_end_month'] = "";
        $data['is_completed'] = "";
        $data['remarks'] = "";
        if (isset($_REQUEST['emp_loan_pkey']) && $_REQUEST['emp_loan_pkey'] != 0) {
            $data_db = $this->EmployeeLoan->find("first", array("conditions" => array(
                    "emp_loan_pkey" => $_REQUEST['emp_loan_pkey']),
            ));
            $data = $data_db['EmployeeLoan'];
            $result = array();
            $current = date("Y/m/d");
            $datechec = (strtotime($data['created_date']));
            $date = date('Y/m/d', $datechec);
            $newdate = strtotime('+3 day', strtotime($date));
            $sumdate = date('Y/m/d', $newdate);
            if ($sumdate < $current) {
                $result['success'] = 1;
                $result['msg'] = "Record(s)   successfully.";
                //echo json_encode($result);
                $this->set("data3", '');
                $this->set("data", $data);
                $this->set("data2", $result);
                //$this->set("data2", $data2);
            } else {
                $current = date("d", strtotime(date("Y/m/d")));
                $datechec = date("d", strtotime($data['created_date']));
                $lol = $current - $datechec;
                // debug($lol);
                $result['success'] = $lol;
                //debug($lol);
                //$fk = date('Y/m/d', $lol);
                //debug($fk);

                $this->set("data2", '');
                $this->set("data3", $lol);
                $this->set("data", $data, "hi", $lol);
            }
        } else {
            $this->set("data3", '');
            $this->set("data2", '');
            $this->set("data", $data);
        }

        //debug($data);
    }
     
    public function getEmi(){
        $this->autoRender = false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;
        
        $month = $arr_form_data['month_year'];
        $emp_pkey = $arr_form_data['empid'];
        
        $joins = array(
          array(
              "table" => "emp_loan",
              "type" => "INNER",
              "alias" => "EmpLoan",
              'foreignKey' => false,
                'conditions' => array('EmployeeLoanInfo.loan_pkey = EmpLoan.emp_loan_pkey')
          )  
        );
        
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("EmployeeLoanInfo.emp_fkey" => $emp_pkey, "EmployeeLoanInfo.loan_month" => $month , "EmployeeLoanInfo.status" => "1"), "fields" => array("EmployeeLoanInfo.*,EmpLoan.*"), "joins" => $joins ));
        $emi_sum = isset($arr_loan_data['0']['EmployeeLoanInfo']['loan_emi'])?$arr_loan_data['0']['EmployeeLoanInfo']['loan_emi']: 0;
        $appnds = '';
        foreach ($arr_loan_data as $val){
            $appnds .= '<option value="'.$val['EmployeeLoanInfo']['loan_pkey'].'" >'.$val['EmpLoan']['loan_amount'].'</option>';
        }
//        debug($arr_loan_data);
        echo json_encode(array("success"=>1,"sum"=>$emi_sum, "data"=> $appnds));
        
    }

    public function update_transfer($loan_pkey = 0) {

        $this->autoRender = false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;
        $taken_month = $arr_form_data['taken_month'];
        $affected_month = $arr_form_data['affected_month'];
        $created_by=$this->Session->read('login_user_id');
        
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $already_exist_month = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","loan_month"=>$affected_month,"paid_status"=>"A")));//This is takes to update the affected months emi with taken montha emi..**BY ARUL P DAS
        // debug($already_exist_month);
        $count_paid = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $transfer_month = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "loan_month" => $taken_month,"paid_status"=>"A")));//This is the month of transfering emi. By ***ARUL P DAS on 15/11/2019
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));
        $this->set("arr_loan_master", $arr_loan_master);

        $find_loan_datas = $this->EmployeeLoanInfo->query("select * from emp_loan_info where loan_pkey = '$loan_pkey' and  loan_month = '$taken_month' and status=1");
        if (count($find_loan_datas) == 0) {
            echo json_encode(array('success' => 0, 'msg' => 'Please Select a Month Within The Loan Period '));
            return;
        }
        if(count($transfer_month)==0){
            echo json_encode(array('success' => 0, 'msg' => 'Please Select a Month Within The Loan Period'));
            return;
        }
        $emp_fkey = $arr_loan_data['0']['EmployeeLoanInfo']['emp_fkey'];
        $attendance_verified_taken = $this->EmployeeLoanInfo->query(" SELECT count(emp_salary_slip_pkey) as count FROM `emp_salary_slip` WHERE `emp_fkey` = '$emp_fkey' AND `month_year` = '$taken_month' AND `end_date_effective` IS NULL ");
        $count1=$attendance_verified_taken[0][0]['count'];
        $attendance_verified_takenaf = $this->EmployeeLoanInfo->query(" SELECT count(emp_salary_slip_pkey) as count FROM `emp_salary_slip` WHERE `emp_fkey` = '$emp_fkey' AND `month_year` = '$affected_month' AND `end_date_effective` IS NULL ");
        $count2=$attendance_verified_takenaf[0][0]['count']; 
        // $loan_verified_takenaf = $this->EmployeeLoanInfo->query(" SELECT * FROM `emp_loan_info` WHERE `emp_fkey` = '$emp_fkey' AND `loan_month` = '$taken_month' AND `paid_status` not in ('A','S') AND loan_pkey='$loan_pkey' and status=1 ");
        $loan_verified_takenaf = $this->EmployeeLoanInfo->query(" SELECT count(emp_loan_info_pkey) as count FROM `emp_loan_info` WHERE `emp_fkey` = '$emp_fkey' AND `loan_pkey`='$loan_pkey' AND `loan_month` = '$taken_month' AND `paid_status`='P' and status=1 and emi_transfer!='Y' ");
        $count3=$loan_verified_takenaf[0][0]['count'];

        if ($count1 > 0) {
            echo json_encode(array('success' => 0, 'msg' => 'Payroll Already Processed on Taken Month '));
            return;
        }
        if ($count2 > 0) {
            echo json_encode(array('success' => 0, 'msg' => 'Payroll Already Processed on Affecting Month '));
            return;
        }
        if ($count3 > 0) {
            echo json_encode(array('success' => 0, 'msg' => 'Loan Already Processed From Taken Month '));
            return;
        }   

        $remarks="EMI Transferred from $taken_month to $affected_month";
        $emi_update_pkey = $transfer_month['0']['EmployeeLoanInfo']['emp_loan_info_pkey'];
        $arr_loan_datas = $this->EmployeeLoanInfo->query(" UPDATE emp_loan_info set loan_emi= 0,amount_to_paid = 0,principle=0,paid_status = 'P',emi_transfer= 'Y',remarks='$remarks' WHERE emp_loan_info_pkey = '$emi_update_pkey' and status=1 ");//Here emi_transfer='Y' indicates the shifted month to amount pay. Which helps to display the shifted emi field.** BY ARUL P DAS on 11-11-2019
        //paid_status[A=Not paid and tenure month,P=Paid month,S=Additional Payment][emi_transfer='Y'=>Transferred emi month]

        // if(count($already_exist_month)==0){//This condition is hided because of maintaining tenure in the view. By ***ARUL P DAS on 16/11/2019
            $arr_empctc_data = array();
            $arr_empctc_data['emp_fkey'] = $arr_loan_data['0']['EmployeeLoanInfo']['emp_fkey'];
            $arr_empctc_data['loan_pkey'] = $loan_pkey;
            // $arr_empctc_data['loan_month'] = $arr_loan_data['0']['EmployeeLoanInfo']['loan_month'];
            $arr_empctc_data['loan_emi'] = $transfer_month['0']['EmployeeLoanInfo']['loan_emi'];
            $arr_empctc_data['amount_to_paid'] = $transfer_month['0']['EmployeeLoanInfo']['amount_to_paid'];
            $arr_empctc_data['principle'] = $transfer_month['0']['EmployeeLoanInfo']['principle'];
            $arr_empctc_data['loan_month'] = $affected_month;
            $arr_empctc_data['created_by'] = $created_by;
            // $arr_empctc_data['remarks'] = "EMI for the month Rs.".$transfer_month['0']['EmployeeLoanInfo']['loan_emi'];
            $arr_empctc_data['remarks']="EMI Transferred (from $taken_month to $affected_month) Rs.".$transfer_month['0']['EmployeeLoanInfo']['loan_emi'];
            $arr_loan_data = $this->EmployeeLoanInfo->save($arr_empctc_data);
        // }else{
        //     $existing_emi=$already_exist_month[0]['EmployeeLoanInfo']['loan_emi'];//This is the emi of affecting month
        //     $existing_amount_to_paid=$already_exist_month[0]['EmployeeLoanInfo']['amount_to_paid'];//This is the amount_to_paid of affecting month
        //     $emi_update_pkey=$already_exist_month['0']['EmployeeLoanInfo']['emp_loan_info_pkey'];
        //     $emp_fkey=$arr_loan_data['0']['EmployeeLoanInfo']['emp_fkey'];
        //     $loan_emi=$transfer_month['0']['EmployeeLoanInfo']['loan_emi']+$existing_emi;//This is to add the taken month emi with affecting month emi. By ***ARUL P DAS on 15/11/2019.
        //     $amount_to_paid=$transfer_month['0']['EmployeeLoanInfo']['amount_to_paid']+$existing_amount_to_paid;
        //     $remarks="EMI for the month Rs.".$loan_emi;
        //     $array_loan_data=$this->EmployeeLoanInfo->query(" UPDATE emp_loan_info set loan_emi= $loan_emi,amount_to_paid = $amount_to_paid,remarks='$remarks' WHERE emp_loan_info_pkey = '$emi_update_pkey' and status=1 ");//This is to update affecting month with taken month. The EMI will be summed. By ARUL P DAS on 15/11/2019.
        // }
        //debug($arr_loan_master);
        echo json_encode(array('success' => 1, 'msg' => 'Employee Loan Saved successfully '));
    }

    //The amount_pay function is used to pay amount in current month, not respect to the Tenure. ***Created by ARUL P DAS on 06/11/2019***
    public function amount_pay($loan_pkey = 0,$amount=0) {
        $this->autoRender = false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $created_by=$this->Session->read('login_user_id');
        $arr_form_data = $this->request->data;
        $user_remarks="";
        $user_remarks = $arr_form_data['remarks'];

        // $arr_loan_master = $this->EmployeeLoan->query("update emp_loan set is_completed = 'Y' where emp_loan_pkey = '$loan_pkey' ");
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        // debug($arr_loan_master);

        $date=date('Y-m');
        //The below code is to check wether the entering amount is already proccessed or the sum of Additional Payment amount is greater than balance amount. BY ********ARUL P DAS on 15/11/2019***********
        $emp_pkey=$arr_loan_master[0]['EmployeeLoan']['emp_fkey'];
        $attendance_verified_taken = $this->EmployeeLoanInfo->query(" SELECT count(emp_salary_slip_pkey) as count FROM `emp_salary_slip` WHERE `emp_fkey` = '$emp_pkey' AND `month_year` = '$date' AND `end_date_effective` IS NULL ");
//        debug($attendance_verified_taken);
        $count1=$attendance_verified_taken[0][0]['count'];
        
//        $attendance_verified_takenaf = $this->EmployeeLoanInfo->query(" SELECT count(emp_salary_slip_pkey) as count FROM `emp_salary_slip` WHERE `emp_fkey` = '$emp_pkey' AND `month_year` = '$date' AND `end_date_effective` IS NULL ");
//        $count2=$attendance_verified_takenaf[0][0]['count']; 
        // $loan_verified_takenaf = $this->EmployeeLoanInfo->query(" SELECT * FROM `emp_loan_info` WHERE `emp_fkey` = '$emp_pkey' AND `loan_month` = '$date' AND `paid_status` != 'A' ");
        $loan_verified_takenaf = $this->EmployeeLoanInfo->query(" SELECT count(emp_loan_info_pkey) as count FROM `emp_loan_info` WHERE `emp_fkey` = '$emp_pkey' AND `loan_pkey`='$loan_pkey' AND `loan_month` = '$date' AND `paid_status`='P' and status=1 and emi_transfer!='Y' ");
        $count3=$loan_verified_takenaf[0][0]['count'];

        $total_standing_instruction=$this->EmployeeLoanInfo->query(" SELECT SUM(loan_emi) as total FROM `emp_loan_info` WHERE `emp_fkey` = '$emp_pkey' AND loan_pkey='$loan_pkey' AND `paid_status` ='S' and status=1 ");
        $sum_of_emi=$this->EmployeeLoanInfo->query(" SELECT SUM(loan_emi) as total FROM `emp_loan_info` WHERE `emp_fkey` = '$emp_pkey' AND loan_pkey='$loan_pkey' AND loan_month='$date' and status=1");
        $balance_details=$this->EmployeeLoanInfo->query(" SELECT SUM(amount_paid) as amount_paid FROM `emp_loan_info` WHERE `emp_fkey` = '$emp_pkey' AND loan_pkey='$loan_pkey' and status=1");
        // debug($total_standing_instruction);
        $total=$total_standing_instruction[0][0]['total'];
        $amount_paid=$balance_details[0][0]['amount_paid'];
        // $opening_balance=$balance_details[0][0]['opening_balance'];
        $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];

        if ($count1 > 0) {
//            debug($count1);
            echo json_encode(array('success' => 0, 'msg' => 'Payroll Already Processed '));
            return;
        }
//        if ($count2 > 0) {
//            echo json_encode(array('success' => 0, 'msg' => 'Payroll Already Processed '));
//            return;
//        }
        if ($count3 > 0) {
            echo json_encode(array('success' => 0, 'msg' => 'Loan Already Processed '));
            return;
        }
        $emi=$sum_of_emi[0][0]['total'];
        $balance_amount=$loan_amount-$amount_paid;
        // if(($loan_amount-$amount_paid)<($total+$amount)){
        if(($loan_amount-($amount_paid+$emi))<$amount){
            //Here checking the total Additional Payment is greater than Balance amount. By ** ARUL P DAS on 15/11/2019
            echo json_encode(array('success' => 0, 'msg' => 'Total Additional Payment exceed Balance Amount'));
            return;
        }
        
        $opening_balance=0;
        $closing_balance=0;
        $principal=0;
        $interestpaid=0;
        // $emi=0;
        // $remarks="Additional Payment for Rs.$amount for $date";
        $remarks="Additional Payment for the month $date of Rs.$amount";
        $result = $this->EmployeeLoanInfo->query("insert into emp_loan_info(opening_balance,emp_fkey,loan_pkey,loan_month,closing_balance,principle,interest,amount_to_paid,loan_emi,amount_paid,paid_status,created_by,remarks,user_remarks)"
                    . "values('$opening_balance','$emp_pkey','$loan_pkey','$date', '$closing_balance','$principal','$interestpaid','$amount','$amount','','S','$created_by','$remarks','$user_remarks')");

        // $result = $this->EmployeeLoanInfo->query("update emp_loan_info set amount_paid='$amount',paid_status='P' where loan_pkey='$loan_pkey'");
        // $last_row=$this->EmployeeLoanInfo->query("select max(loan_month) as month from emp_loan_info where paid_status='A' and loan_pkey='$loan_pkey'");
        // $last_month=$last_row[0][0]['month'];
        $month_details=$this->EmployeeLoanInfo->query("select loan_month,loan_emi,emp_loan_info_pkey from emp_loan_info where paid_status='A' and loan_pkey='$loan_pkey' and emi_transfer!='Y' and loan_emi!=0 and status=1 order by loan_month desc");
        $balance=$amount;
        foreach ($month_details as $key => $value) {//This loop is to deduct the Additional Payment from last months EMI.By *** ARUL P DAS on 15/11/2019
            $emi=$value['emp_loan_info']['loan_emi'];
            $month=$value['emp_loan_info']['loan_month'];
            $emp_loan_info_pkey=$value['emp_loan_info']['emp_loan_info_pkey'];
            if($balance>=$emi){
                $balance=$balance-$emi;
                if($balance>=0){
                    // echo "update as 0 -".$month." <br>";
                    $update_row=$this->EmployeeLoanInfo->query("update emp_loan_info set principle=0,interest=0,amount_to_paid=0,loan_emi=0,closing_balance=0,opening_balance=0,remarks='Loan amount already paid on $date' where loan_month='$month' and paid_status='A' and loan_pkey='$loan_pkey' and emp_loan_info_pkey='$emp_loan_info_pkey' and emi_transfer!='Y' and status=1");

                }else{
                    // echo "update balance ".$emi."-".$month."<br>";
                    // $update_row=$this->EmployeeLoanInfo->query("update emp_loan_info set principle=0,interest=0,amount_to_paid=0,loan_emi=0,closing_balance=0,opening_balance=0 where loan_month='$month' and paid_status='A' and loan_pkey='$loan_pkey' and emi_transfer!='Y'");
                    $balance=0;
                }
            }else{
                $final_balance=$emi-$balance;
                // echo "Update ".$final_balance." date :".$month."<br>";
                $update_row=$this->EmployeeLoanInfo->query("update emp_loan_info set amount_to_paid=$final_balance,loan_emi=$final_balance,remarks='EMI for the month Rs.$final_balance' where loan_month='$month' and paid_status='A' and loan_pkey='$loan_pkey' and emp_loan_info_pkey='$emp_loan_info_pkey' and emi_transfer!='Y' and status=1");
                $balance=0;
            }
            if($balance<=0){
                break;
            }
        }
        
        // debug($last_month);
        // debug($update_row);

        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $total = $count_paid['0']['0']['Totalpaid'];
        $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $balance_amount=$loan_amount-$total;
        //The below code is to set the Loan is completed, if the balance_amount reaches zero.
        if($balance_amount==0 || $balance_amount<0){
            $arr_loan_master = $this->EmployeeLoan->query("update emp_loan set is_completed = 'Y' where emp_loan_pkey = '$loan_pkey' and status=1 ");
            $arr_loan_master = $this->EmployeeLoan->query("update emp_loan_info set paid_status = 'P' where loan_pkey = '$loan_pkey' and status=1 ");
        }
        // $arr_loan_master = $this->EmployeeLoan->query("update emp_loan_info set amount_paid = loan_emi where loan_pkey = '$loan_pkey' ");

        if ($result>0) {
            echo json_encode(array('success' => 1, 'msg' => 'Additional Payment Saved successfully '));
        }else{
            echo json_encode(array('success' => 0, 'msg' => 'Additional Payment Cannot Saved successfully '));
        }
    }
    public function update($loan_pkey = 0) {

        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1"),"order"=>"loan_month"));//Order by the loan month.
        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $emi_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(loan_emi) as Totalemi"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));


        $loan_months=$this->EmployeeLoanInfo->query("SELECT DISTINCT loan_month FROM emp_loan_info where loan_pkey='$loan_pkey' and status=1 and paid_status='A' and loan_emi!=0 order by loan_month");
        $last_month=$this->EmployeeLoanInfo->query("SELECT DISTINCT MAX(loan_month) as last FROM emp_loan_info where loan_pkey='$loan_pkey' and status=1 and paid_status='A' and loan_emi!=0");
        $start_month='';
        if(count($loan_months)>0){
            $start_month= $loan_months[0]['emp_loan_info']['loan_month'];
        }

        $loan_months_new=$this->EmployeeLoanInfo->query("SELECT DISTINCT loan_month FROM emp_loan_info where loan_pkey='$loan_pkey' and status=1 and paid_status='A' and loan_month!='$start_month' and loan_emi!=0 order by loan_month");
        $this->set("loan_months_new", $loan_months_new);

        $this->set("loan_months", $loan_months);
        $this->set("last_month", $last_month);

        $this->set("arr_loan_master", $arr_loan_master);
        $total = $count_paid['0']['0']['Totalpaid'];
        $emi_pay = $emi_paid['0']['0']['Totalemi'];
        // $balance_amount = isset($balance['0']['EmployeeLoanInfo']['closing_balance']) ? $balance['0']['EmployeeLoanInfo']['closing_balance'] : null;
        $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $balance_amount=$loan_amount-$total;
        $this->set("emi_pay", $emi_pay);
        $this->set("total", $total);
        $this->set("balance_amount", $balance_amount);
        $this->set("arr_loan_data", $arr_loan_data);
        //debug($arr_loan_master);
    }
    public function checkmonth($from_month=0,$loan_pkey = 0){
        // debug($from_month);
        $this->autoRender=false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $loan_months_new=$this->EmployeeLoanInfo->query("SELECT DISTINCT loan_month FROM emp_loan_info where loan_pkey='$loan_pkey' and status=1 and paid_status='A' and loan_emi!=0 and loan_month!='$from_month' order by loan_month");
        $this->set("loan_months_new", $loan_months_new);
        $data='';
        foreach ($loan_months_new as $key => $value) {
            $data.="<option>".$value['emp_loan_info']['loan_month']."</option>";
        }
        $last_month=$this->EmployeeLoanInfo->query("SELECT DISTINCT MAX(loan_month) as last FROM emp_loan_info where loan_pkey='$loan_pkey' and status=1 and paid_status='A' and loan_emi!=0");
        $start_month = (strtotime($last_month[0][0]['last']));
        $month = date('Y-m', strtotime("+1 month", $start_month));
        $data.="<option value='$month'>".$month."</option>";
        echo json_encode(array('success' => 1, 'data' => $data));

    }

    public function completed($loan_pkey = 0) {
        $this->autoRender = false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->query("update emp_loan set is_completed = 'Y' where emp_loan_pkey = '$loan_pkey' and status=1 ");
        $arr_loan_master = $this->EmployeeLoan->query("update emp_loan_info set amount_paid = loan_emi,paid_status = 'P' where loan_pkey = '$loan_pkey' and status=1 ");
        echo 1;
    }

    public function emploan() {
        
    }

    public function upload() {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
//        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
    }

    public function viewloan($loan_pkey = 0) {
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1"),"order"=>"loan_month"));//Order by the loan month.
        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $shifted_times_count = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(emi_transfer) as shifted_times"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","emi_transfer"=>"Y")));
        // $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(amount_paid) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","loan_emi"=>"0","amount_paid >"=>"0")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(loan_emi) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","paid_status"=>"S")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition_payrolled = $this->EmployeeLoanInfo->query("select count(amount_paid) as paid_count from emp_loan_info where loan_pkey=$loan_pkey and status=1 and paid_status='P' and emi_transfer!='Y'");//paid count by ARUL P DAS on 14/11/2019
        $emi_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(loan_emi) as Totalemi"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));
        $standing_instruction_payed = $this->EmployeeLoanInfo->query("select emp_loan_info_pkey from emp_loan_info where paid_status='P' and loan_pkey=$loan_pkey and remarks like '%Standing%'");//paid count by ARUL P DAS on 14/11/2019
        // debug($standing_instruction_payed);
        $list_of_standing_instruction=array();
        $increment=0;
        foreach ($standing_instruction_payed as $key => $value) {
            $list_of_standing_instruction[$increment]=$value['emp_loan_info']['emp_loan_info_pkey'];
            $increment++;
        }
        $this->set("list_of_standing_instruction",$list_of_standing_instruction);
        $this->set("arr_loan_master", $arr_loan_master);

        $total = $count_paid['0']['0']['Totalpaid'];
        $count_paid_times = $count_paid_addition['0']['0']['count'];
        $count_paid_payroll_times = $count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019
        // debug($count_paid_addition_payrolled);
        $count_all_times=count($arr_loan_data);
        $shifted_times=$shifted_times_count['0']['0']['shifted_times'];
        $emi_pay = $emi_paid['0']['0']['Totalemi'];
        $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $tenure=$arr_loan_master[0]['EmployeeLoan']['tenure'];

        $data=array();
        $data['tenure']=$arr_loan_master[0]['EmployeeLoan']['tenure'];
        $data['count_all_times']=count($arr_loan_data);
        $data['shifted_times']=$shifted_times_count['0']['0']['shifted_times'];
        $data['count_paid_times']=$count_paid_addition['0']['0']['count'];
        $data['count_paid_payroll_times']=$count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019

        $this->set("data",$data);//This data array is created for sending the all data as a single data.
        // debug($data);
        // $balance_amount = isset($balance['0']['EmployeeLoanInfo']['closing_balance']) ? $balance['0']['EmployeeLoanInfo']['closing_balance'] : null;
        //The balance amount is subtracting total paid amount from loan amount.***ARUL P DAS on 06/11/2019
        $balance_amount=$loan_amount-$total;
        $this->set("emi_pay", $emi_pay);
        $this->set("total", $total);
        $this->set("tenure", $tenure);
        $this->set("count_paid_times", $count_paid_times);
        $this->set("count_paid_payroll_times", $count_paid_payroll_times);
        $this->set("count_all_times", $count_all_times);
        $this->set("shifted_times",$shifted_times);
        $this->set("balance_amount", $balance_amount);
        $this->set("arr_loan_data", $arr_loan_data);
        //debug($arr_loan_master);
        $opening_balance=0;$closing_balance=0;
        //edited by megha 
        $temp_opening_balance=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $remarks="";
        foreach($arr_loan_data as $loan) {
            $loan_info_pkey=$loan['EmployeeLoanInfo']['emp_loan_info_pkey'];
            $paid_status=$loan['EmployeeLoanInfo']['paid_status'];
            $emi_transfer=$loan['EmployeeLoanInfo']['emi_transfer'];
            // if($paid_status=="P" && $emi_transfer!='Y'){
            //     $paid=$this->EmployeeLoanInfo->query("update emp_loan_info set remarks='Paid' where emp_loan_info_pkey=$loan_info_pkey and status=1");
            // }
            if($opening_balance==0){$opening_balance=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];}
            $closing_balance=$opening_balance-$loan['EmployeeLoanInfo']['amount_paid'];
//            if($loan['EmployeeLoanInfo']['loan_emi']=='0'){
//                $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='0', closing_balance='0' where emp_loan_info_pkey=$loan_info_pkey and status=1");
//            }else{
//                $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='$opening_balance', closing_balance='$closing_balance' where emp_loan_info_pkey=$loan_info_pkey and status=1");
//            }
            //edited by megha 
            if($loan['EmployeeLoanInfo']['loan_emi']=='0'){
                $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='0', closing_balance='0' where emp_loan_info_pkey=$loan_info_pkey and status=1");
            }else{
                if($loan['EmployeeLoanInfo']['loan_emi']>$temp_opening_balance){
                    $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='$opening_balance', closing_balance='$closing_balance',loan_emi='$temp_opening_balance' where emp_loan_info_pkey=$loan_info_pkey and status=1");
                }else{
                    $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='$opening_balance', closing_balance='$closing_balance' where emp_loan_info_pkey=$loan_info_pkey and status=1");
                }
            }
            $temp_opening_balance=$temp_opening_balance-$loan['EmployeeLoanInfo']['loan_emi'];
            //end
            $opening_balance=$closing_balance;
        }


        //The below code is to update the details into emp_loan_info table.. By ARUL P DAS on 13/11/2019
        // $i = 0;
        // $opening_balance=0;
        // $closing_balance=0;
        // $count=1;
        // $flag=0; 
        // $zero_case=0;
        // $sum_of_emi=0;
        // // if((($count_all_times-$tenure)+$shifted_times)==($count_paid_payroll_times)){
        // //     $limit=$count_all_times-($count_paid_payroll_times);
        // // }else{
        // //     $limit=$count_all_times;
        // // }//Here checking if the (Total-Tenure)+shift==paid_count., Then limit=Total-paid_count. else, limit=total. This code *** By ARUL P DAS on 14-11-2019
        //  $additional_all=($count_all_times-$tenure);

        // if((($count_all_times-$tenure)+($additional_all-$count_paid_times))==($count_paid_payroll_times)){
        //     $limit=$count_all_times-($count_paid_payroll_times-($additional_all-$count_paid_times));
        // }else{
        //     $limit=$count_all_times;
        // }//Here checking if the (Total-Tenure)+shift+(additional_all-standing_instruction)==paid_count., Then limit=Total-paid_count-(paid_count-(additional_all-standing_instruction)). else, limit=total. This code *** By ARUL P DAS on 14-11-2019
        // foreach($arr_loan_data as $loan) {
        //     $remarks="";
        //     $i+=1;
        //     if($opening_balance==0){$opening_balance=$loan_amount;}
        //     $closing_balance=$opening_balance-$loan['EmployeeLoanInfo']['amount_paid'];
        //     if($loan['EmployeeLoanInfo']['loan_emi']>$opening_balance){
        //         $diff=$loan['EmployeeLoanInfo']['loan_emi']-$opening_balance;
        //         $emi=$loan['EmployeeLoanInfo']['loan_emi']-$diff;
        //     }else{
        //         $emi=$loan['EmployeeLoanInfo']['loan_emi'];
        //     }
        //     if($opening_balance==0){$flag=1;}
        //     $flag2=0;
        //     // if($emi==0 && $loan['EmployeeLoanInfo']['amount_paid']!=0){$flag2=1;}
        //     if($loan['EmployeeLoanInfo']['paid_status']=="S" || $loan['EmployeeLoanInfo']['amount_paid']!=0){$flag2=1;}//This is because, The additional paid amount. which always shows in the view. By *** ARUL P DAS on 14/11/2019

        //     if($loan['EmployeeLoanInfo']['paid_status']=="P"){
        //         if($loan['EmployeeLoanInfo']['emi_transfer']=="Y"){
        //             /*echo '#d3d3d3';*/$zero_case=1;
        //         }else{
        //             //The below code is to check if the amount payed as Additional Payment or normarl EMI. By ****ARUL P DAS on 15/11/2019****
        //             if (in_array($loan['EmployeeLoanInfo']['emp_loan_info_pkey'], $list_of_standing_instruction)){
        //                 // $remarks=",remarks='Paid Additional Payment'";//This separates the paid emi and paid Additional Payment. But now im changing both to paid. By ARUL P DAS on 15/11/2019
        //                 $remarks=",remarks='Paid'";
        //             }else{
        //                 // $remarks=",remarks='Paid EMI'";
        //                 $remarks=",remarks='Paid'";
        //             }
        //             // debug($list_of_standing_instruction);
        //         }
        //     }

        //     $update_paid_status="";
        //     if($zero_case==1){
        //         $opening_balance=0;
        //         $emi=0;
        //         $interest=0;
        //         $principle=0;
        //         $amount_paid=0;
        //         $amount_to_paid=0;
        //         $closing_balance=0;
        //         $update_paid_status=",paid_status='A'";//Temporary assign.Beacuse of displaying Remarks as Paid
        //         // $update_paid_status=",paid_status='P'";Rolled EMI set as Paid. By *** ARUL P DAS on 14/11/2019
        //         //This is for ROLLED OUT...
        //     }else{
        //         //$limit=0;//The limit was setted for setting last rows of an employee loan details, if he paid additional amount in any case. By from further updates, The additional amount is turned as Additional Payment. That will not directly affect to the paid amount. So the last emi shold be stay unchanged.
        //         // if($count>$limit || $flag==1){
        //         // if($loan['EmployeeLoanInfo']['paid_status']=="A" && $loan['EmployeeLoanInfo']['emi_transfer']!="Y"){
        //         //     $sum_of_emi=$sum_of_emi+$emi;
        //         //     if($count>$limit){
        //         //         if ($sum_of_emi<$opening_balance) {
        //         //             $flag2=1;
        //         //         }
        //         //     }
        //         // }
        //         if($count>$limit || $flag==1){    
        //             if($flag2){
        //                 $interest=$loan['EmployeeLoanInfo']['interest'];
        //                 $principle=$loan['EmployeeLoanInfo']['principle'];
        //                 $amount_paid=$loan['EmployeeLoanInfo']['amount_paid'];
        //                 $amount_to_paid=$loan['EmployeeLoanInfo']['amount_to_paid'];
        //                 $remarks=$loan['EmployeeLoanInfo']['remarks'];
        //             }else{
        //                 $opening_balance=0;
        //                 $emi=0;
        //                 $interest=0;
        //                 $principle=0;
        //                 $amount_paid=0;
        //                 $amount_to_paid=0;
        //                 $closing_balance=0;
        //                 $update_paid_status=",paid_status='A'";
        //                 $remarks='Loan EMI already Paid'";
        //             }
        //         }else{
        //             $interest=$loan['EmployeeLoanInfo']['interest'];
        //             $principle=$loan['EmployeeLoanInfo']['principle'];
        //             $amount_paid=$loan['EmployeeLoanInfo']['amount_paid'];
        //             $amount_to_paid=$loan['EmployeeLoanInfo']['amount_to_paid'];
        //             // echo $loan['EmployeeLoanInfo']['remarks'];
        //         }
                
        //     }
        //     $emp_loan_info_pkey=$loan['EmployeeLoanInfo']['emp_loan_info_pkey'];

        //     $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance=$opening_balance,loan_emi=$emi,interest=$interest,principle=$principle,amount_to_paid=$amount_to_paid,amount_paid=$amount_paid,closing_balance=$closing_balance $update_paid_status $remarks where emp_loan_info_pkey=$emp_loan_info_pkey");
        //     if($result>0){
        //         // echo "<script>alert('Successfully Updated');</script>";
        //     }else{
        //         // echo "<script>alert('Error in updation');</script>";
        //     }

        //     if($opening_balance<=$emi){$flag=1;}
        //     $opening_balance=$closing_balance;
        //     $zero_case=0;
        //     $count++;
        // }
    }

    public function downloads($loan_pkey = 0) {
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1"),"order"=>"loan_month"));//Order by the loan month.
        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $shifted_times_count = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(emi_transfer) as shifted_times"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","emi_transfer"=>"Y")));
        // $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(amount_paid) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","loan_emi"=>"0","amount_paid >"=>"0")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(loan_emi) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","paid_status"=>"S")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition_payrolled = $this->EmployeeLoanInfo->query("select count(amount_paid) as paid_count from emp_loan_info where loan_pkey=$loan_pkey and status=1 and paid_status='P'");//paid count by ARUL P DAS on 14/11/2019
        $emi_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(loan_emi) as Totalemi"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));
        $standing_instruction_payed = $this->EmployeeLoanInfo->query("select emp_loan_info_pkey from emp_loan_info where paid_status='P' and loan_pkey=$loan_pkey and remarks like '%Standing%'");//paid count by ARUL P DAS on 14/11/2019
        // debug($standing_instruction_payed);
        $list_of_standing_instruction=array();
        $increment=0;
        foreach ($standing_instruction_payed as $key => $value) {
            $list_of_standing_instruction[$increment]=$value['emp_loan_info']['emp_loan_info_pkey'];
            $increment++;
        }
        $this->set("list_of_standing_instruction",$list_of_standing_instruction);
        $this->set("arr_loan_master", $arr_loan_master);
        $total = $count_paid['0']['0']['Totalpaid'];
        $count_paid_times = $count_paid_addition['0']['0']['count'];
        $count_paid_payroll_times = $count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019
        // debug($count_paid_addition_payrolled);
        $count_all_times=count($arr_loan_data);
        $shifted_times=$shifted_times_count['0']['0']['shifted_times'];
        $emi_pay = $emi_paid['0']['0']['Totalemi'];
        $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $tenure=$arr_loan_master[0]['EmployeeLoan']['tenure'];

        $data=array();
        $data['tenure']=$arr_loan_master[0]['EmployeeLoan']['tenure'];
        $data['count_all_times']=count($arr_loan_data);
        $data['shifted_times']=$shifted_times_count['0']['0']['shifted_times'];
        $data['count_paid_times']=$count_paid_addition['0']['0']['count'];
        $data['count_paid_payroll_times']=$count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019

        $this->set("data",$data);//This data array is created for sending the all data as a single data.
        // debug($data);
        // $balance_amount = isset($balance['0']['EmployeeLoanInfo']['closing_balance']) ? $balance['0']['EmployeeLoanInfo']['closing_balance'] : null;
        //The balance amount is subtracting total paid amount from loan amount.***ARUL P DAS on 06/11/2019
        $balance_amount=$loan_amount-$total;
        $this->set("emi_pay", $emi_pay);
        $this->set("total", $total);
        $this->set("tenure", $tenure);
        $this->set("count_paid_times", $count_paid_times);
        $this->set("count_paid_payroll_times", $count_paid_payroll_times);
        $this->set("count_all_times", $count_all_times);
        $this->set("shifted_times",$shifted_times);
        $this->set("balance_amount", $balance_amount);
        $this->set("arr_loan_data", $arr_loan_data);

        //$content ="<h2>hi</h2>";
        $view = new View($this, false);
        $view_output = $view->render('download');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
        try {
            $html2pdf = new HTML2PDF('L', 'A4', 'en');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
            //$html2pdf->writeHTML($content);
            $html2pdf->writeHTML($view_output);
            $html2pdf->Output('Loandetails.pdf', 'D');
            $this->render('download');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }
    
    public function employeeemiloansave(){
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->LoanEmi->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['amt'] = $arr_form_data['loan_emi'];
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        
        $result = $this->LoanEmi->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Employee loan Save successfully";
        echo json_encode($resp);
    }

//save 
    public function employeeloansave() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        $count = $arr_form_data['tenure'];
        $loan_amt = $arr_form_data['loan_amount'];
        $emp_pkey = $arr_form_data['emp_fkey'];
        $interest = $arr_form_data['intrest_rate'];
        $emi = $arr_form_data['emi_amount'];
        $next = strtotime($arr_form_data['emi_start_month']);
        $months = $arr_form_data['emi_end_month'];
        $closing_balance = $loan_amt;
        $result = $this->EmployeeLoan->save($arr_form_data);
        $loan_pkey = $this->EmployeeLoan->getLastInsertId();
        $created_by=$this->Session->read('login_user_id');
        $arr_loan_data = array();
        $temp_opening_balance = $emi * $count;
        //$temp_opening_balance=$arr_form_data['loan_amount'];//This is to calculate opening balance in the case of decimal point value in EMI. By ***ARUL P DAS on 11/1/2020
        for ($i = 0; $i < $count; $i++) {
            $opening_balance = $closing_balance;
            $interests = $opening_balance * $interest / 100;
            $interestpaid = $interests * 1 / 12;
            $principal = $emi - $interestpaid;
            $closing_balance = $opening_balance - $principal;
            $arr_loan_data['opening_balance'] = $opening_balance;
            $arr_loan_data['closing_balance'] = $closing_balance;
            $arr_loan_data['principle'] = $principal;
            $arr_loan_data['interest'] = $interestpaid;
            $arr_loan_data['amount_to_paid'] = $emi;
            $arr_loan_data['loan_emi'] = $emi;
            $remarks="EMI for the month Rs.".round($emi);//The remark is by ARUL P DAS on 13/11/2019

            //debug($starts);
            $month = date("Y-m", $next);
            //debug("opening Balance :".$opening_balance.", EMI :".$emi.", Inetrest: ".$interestpaid.", Principal: ".$principal.", Closing Balance: ".$closing_balance);
            
            //When the EMI greater than opening balance. That is the case of loan amount 525 and tenure 2. EMI decimal value is 262.5 and the rounded value is 263.
            //And this code is by ***ARUL P DAS on 11/1/2020
            if(round($emi)>$temp_opening_balance){
                $remarks="EMI for the month Rs.".round($temp_opening_balance);//The remark is by ARUL P DAS on 13/11/2019
                $result = $this->EmployeeLoanInfo->query("insert into emp_loan_info(opening_balance,emp_fkey,loan_pkey,loan_month,closing_balance,principle,interest,amount_to_paid,loan_emi,created_by,remarks)"
                    . "values('$temp_opening_balance','$emp_pkey','$loan_pkey','$month', '$closing_balance','$temp_opening_balance','$interestpaid','$temp_opening_balance','$temp_opening_balance','$created_by','$remarks')");
            }else{
                $result = $this->EmployeeLoanInfo->query("insert into emp_loan_info(opening_balance,emp_fkey,loan_pkey,loan_month,closing_balance,principle,interest,amount_to_paid,loan_emi,created_by,remarks)"
                    . "values('$opening_balance','$emp_pkey','$loan_pkey','$month', '$closing_balance','$principal','$interestpaid','$emi','$emi','$created_by','$remarks')");
            }
            $starts = date("Y-m", strtotime("+1 month", $next));
            $next = strtotime($starts);
            $temp_opening_balance=$temp_opening_balance-round($emi);//This is to calculate the opening balance with rounded emi.
        }
        //The below case is the last closing balance is greater than 0.Which means the loan is not closed or completed and the tenure is over.
        //In the case of loan amount 800 and tenure 6. The decimal EMI value is 133.33 and rounded EMI is 133. The balance of last tenure will be 2 rupees.
        //This code is by ***ARUL P DAS on 11/1/2020
        if($temp_opening_balance>0){
            $emi=round($emi)+$temp_opening_balance;
            $remarks="EMI for the month Rs.".round($emi);//The remark is by ARUL P DAS on 11/1/2020
            $result=$this->EmployeeLoanInfo->query("update emp_loan_info set principle='$emi',amount_to_paid='$emi',loan_emi='$emi',remarks='$remarks', closing_balance='0' where loan_month='$month' and loan_pkey='$loan_pkey' and status=1");
        }
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Employee loan Save successfully";
        echo json_encode($resp);
    }

//list            
    public function employeeloanlist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;

        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $slctmonth = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        // if(isset($arr_request_data['name']) && $arr_request_data['name'] =='1')
        // {
        //      $conditions = "and au.is_completed = 'Y'";
        // }
        // else{    
        //      $conditions = "and au.is_completed = 'N'";
        // }
        $conditions='';//Here selecting all completed and incompleted Loans together to display.***ARUL P DAS on 04-11-2019***
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if ($slctmonth != '') {
//            $mnthval = explode ("-", $slctmonth);
//            $selmonth = $mnthval[1];
            $branch_month = "and au.emi_start_month='$slctmonth'";                
        }else
        {
            $branch_month = '';
        }
        
        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey = $emp";
        } else {
            $emp_condition = '';
        }
        
        if ($emp_fkey != '') {
            $emp_condition = "and au.emp_fkey=$emp_fkey";
        }
        
        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }
        // echo "Month:".$branch_month."<br>";
        // echo "Emp Condition :".$emp_condition."<br>";
        // echo "Branch  condition:".$branch_condition."<br>";
//        $counts = $this->EmployeeLoan->query("select COUNT(*)
//                            from emp_details ed
//                             join emp_loan au on (ed.emp_pkey = au.emp_fkey) 
//                            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
//                            INNER join employee_info ei on (ed.emp_pkey= ei.emp_pkey)
//                            where au.status=1 
//                            $emp_condition $branch_condition $branch_month $conditions");
        $counts = $this->EmployeeLoan->query("select COUNT(*)
                            from emp_details ed 
                            INNER join emp_loan au on (ed.emp_pkey = au.emp_fkey) 
                            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
                            INNER join employee_info ei on (ed.emp_pkey= ei.emp_pkey)
                            where au.status=1 
                            $emp_condition $branch_condition $branch_month $conditions and ed.status=1 ");
        $count = $counts[0][0]['COUNT(*)'];
        // debug($emp_condition);
                
        $arr_att = $this->EmployeeLoan->query("select au.*,ep.emp_company_id,ei.EmpName,(select sum(amount_paid) from emp_loan_info where emp_loan_info.loan_pkey = au.emp_loan_pkey $emp_condition)as loan_paid
                            from emp_details ed 
                            INNER join emp_loan au on (ed.emp_pkey = au.emp_fkey) 
                            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
                            INNER join employee_info ei on (ed.emp_pkey= ei.emp_pkey)
                            where au.status=1 
                            $emp_condition $branch_condition $branch_month $conditions and ed.status=1 "
                . " ORDER BY created_date desc"
                . " limit $limit offset $ofst");
                
//        $arr_att = $this->EmployeeLoan->query("select au.*,ep.emp_company_id,ei.EmpName
//                            from emp_details ed
//                            INNER join emp_loan au on (ed.emp_pkey = au.emp_fkey) 
//                            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
//                            INNER join employee_info ei on (ed.emp_pkey= ei.emp_pkey)
//                            where au.status=1 
//                            $emp_condition $branch_condition "
//                . " ORDER BY created_date desc"
//                . " limit $limit  offset $ofst  ");        
    //   debug($arr_att);
        
        $out = array();
        foreach ($arr_att as $key => $value) {
 //debug($value);
            $end_date = isset($value['au']['emi_end_month']) ? $value['au']['emi_end_month'] : date('Y-m-d');
            //debug($end_date);

            $date = explode("-", $end_date);
            $year = (isset($date[0]) ? $date[0] : 0);
            $mon = (isset($date[1]) ? $date[1] : 0);
            if ($mon == 1) {
                $mon = 'Jun';
            } else if ($mon == 2) {
                $mon = 'Feb';
            } else if ($mon == 3) {
                $mon = 'Mar';
            } else if ($mon == 4) {
                $mon = 'Apr';
            } else if ($mon == 5) {
                $mon = 'May';
            } else if ($mon == 6) {
                $mon = 'Jun';
            } else if ($mon == 7) {
                $mon = 'Jul';
            } else if ($mon == 8) {
                $mon = 'Aug';
            } else if ($mon == 9) {
                $mon = 'Sep';
            } else if ($mon == 10) {
                $mon = 'Oct';
            } else if ($mon == 11) {
                $mon = 'Nov';
            } else if ($mon == 12) {
                $mon = 'Dec';
            }

            $getmonth = $mon . "-" . $year;
            
            //debug($getmonth);
            //debug($date);
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
            $out['empid'] = isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '';
            $out['emp_loan_pkey'] = isset($value['au']['emp_loan_pkey']) ? $value['au']['emp_loan_pkey'] : '';
            $out['loan_amount'] = isset($value['au']['loan_amount']) ? $value['au']['loan_amount'] : '';
            $out['tenure'] = isset($value['au']['tenure']) ? $value['au']['tenure'] : '';
            $out['intrest_rate'] = isset($value['au']['intrest_rate']) ? $value['au']['intrest_rate'] : '0';
            //$out['emi_amount'] = isset($value['au']['emi_amount']) ? $value['au']['emi_amount'] : '';
            $out['emi_amount'] = isset($value['au']['emi_amount']) ? round($value['au']['emi_amount']) : '';
            $out['emi_end_month'] = isset($value['au']['emi_end_month']) ? date('M-Y', strtotime($value['au']['emi_end_month'])) : '';
            $out['emi_start_month'] = isset($value['au']['emi_start_month']) ? date('M-Y', strtotime($value['au']['emi_start_month'])) : '';
            $out['is_completed'] = isset($value['au']['is_completed']) ? $value['au']['is_completed'] : '';
            $out['remarks'] = isset($value['au']['remarks']) ? $value['au']['remarks'] : '';
            //$out['loan_paid'] = isset($value['0']['loan_paid']) ? $value['0']['loan_paid'] : '';
            $out['loan_paid'] = isset($value['0']['loan_paid']) ? $value['0']['loan_paid'] : '0';
            $loan_amount=isset($value['au']['loan_amount']) ? $value['au']['loan_amount'] : '';
            $paid_amount=isset($value['0']['loan_paid']) ? $value['0']['loan_paid'] : '';
//edited by megha on 07/03/2024
            //            $out['balance_amount']=$loan_amount-$paid_amount;//This is to calculate balance amount. By ***ARUL P DAS on 21/11/2019
            $balance_amount=$loan_amount-$paid_amount;//This is to calculate balance amount. By ***ARUL P DAS on 21/11/2019
            $out['balance_amount']=($balance_amount<=0)?'0':$balance_amount;//This is rounding the amount to zero if it is less than zero. By ***ARUL P DAS on 9/1/2020
            $resp_att["rows"][$key] = $out;
        }
   
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    public function jsons($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = "and branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        if ($q != null) {
            //$q_condition = "and first_name like '%$q%'";
             $q_condition = "and first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ";
        } else {
            $q_condition = "";
        }
        
//        if ($month != '') {
////          $mnthval = explode ("-", $slctmonth);
////          $selmonth = $mnthval[1];
//            $branch_month = "and au.emi_start_month='$month'";                
//        }else
//        {
//            $branch_month = '';
//        }
 
        $branch_array = $this->EmployeeDetails->query("select *,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_details.status = 1 $branch_condition $q_condition ORDER BY first_name ASC ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "all", "text" => "ALL");
//      $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function jsons_form($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = "and branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        if ($q != null) {
            $q_condition = "and ( first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ) ";
        } else {
            $q_condition = "";
        }

        $branch_array = $this->EmployeeDetails->query("select *,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where status = 1 $branch_condition $q_condition ORDER BY first_name ASC ");
        $array = array();
        $branch = array();
        $branch[] = array("id" => "all", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    
    //main page dropdown       
    public function ctcupload() {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
    }

//delete
    public function deleteEmployeeloan() {
        $this->autoRender = FALSE;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_loan_pkey"])) {
            $ar_ids = explode(",", $_REQUEST["emp_loan_pkey"]);
            //debug($ar_ids);
            $this->EmployeeLoan->updateAll(
                    array('EmployeeLoan.status' => 0), array('EmployeeLoan.emp_loan_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

//checking salary slip
    public function salarycheck() {
        $arr_request = $this->request->data;
        //debug($arr_request);
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $form_month = $arr_request['month_year'];
        $month = explode("-", $form_month);
        $year = $month[0];
        $mon = $month[1];
        $set_month = $year . '-' . $mon;
        $empfkey = $arr_request['empid'];
        $arr_salary_month_check = $this->EmployeeLoan->query("select emp_salary_slip.salary_amount FROM   emp_salary_slip WHERE month_year= '$set_month' AND emp_fkey= '$empfkey' and end_date_effective IS NULL ");
        // debug($arr_salary_month_check);
        //$extingsalary=$arr_salary_month_check[0]['emp_salary_slip']['salary_amount'];
        // debug($arr_salary_month_check);
        $this->set('arr_salary_month_check', $arr_salary_month_check);
        $data = array();
        $data['msg'] = "Salary already processed";
        $data['rows'] = $arr_salary_month_check;
        echo json_encode($data);
    }
    //This is created to check wether payroll already processed or not. When onchange in amount field. By ***ARUL P DAS on 21/11/2019
    public function payroll_check_amount_pay($emp_fkey=0) {
        // $arr_request = $this->request->data;
        //debug($arr_request);
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $date=date('Y-m');
        // $empfkey = $arr_request['empid'];
        $arr_salary_month_check = $this->EmployeeLoan->query("select count(emp_salary_slip.salary_amount) as count FROM emp_salary_slip WHERE month_year= '$date' AND emp_fkey= '$emp_fkey' and end_date_effective IS NULL ");
        // debug($arr_salary_month_check);
        //$extingsalary=$arr_salary_month_check[0]['emp_salary_slip']['salary_amount'];
        // debug($arr_salary_month_check);
//        $this->set('arr_salary_month_check', $arr_salary_month_check);
//        debug($arr_salary_month_check);
        $data = array();
        $data['msg'] = "Salary already processed";
        $data['success'] = $arr_salary_month_check[0][0]['count'];
        echo json_encode($data);
    }

    public function downloadexcels($loan_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1"),"order"=>"loan_month"));//Order by the loan month.
        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $shifted_times_count = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(emi_transfer) as shifted_times"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","emi_transfer"=>"Y")));
        $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(loan_emi) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","paid_status"=>"S")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $emi_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(loan_emi) as Totalemi"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));

        $user_name = $this->Session->read('user_name');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $str_company_code = $this->Session->read('company_code');

        // $total = $count_paid['0']['0']['Totalpaid'];
        // $count_paid_times = $count_paid_addition['0']['0']['count'];
        // $count_all_times=count($arr_loan_data);
        // $shifted_times=$shifted_times_count['0']['0']['shifted_times'];
        // $emi_pay = $emi_paid['0']['0']['Totalemi'];
        // $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        // $tenure=$arr_loan_master[0]['EmployeeLoan']['tenure'];
        // $balance_amount=$loan_amount-$total;//The balance amount is subtracting total paid amount from loan amount.***ARUL P DAS on 06/11/2019
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        // $file_name = 'Employee Loan Details.xls';
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_loan_details.xls" : "employeeloandetails_" . strtotime() . ".xls";
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setCellValueByColumnAndRow(0, 1, $arr_loan_master['0']['EmployeeInfo']['EmpName'] . "'s Loan Details");
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
        for ($col = 'A'; $col !== 'N'; $col++) {
            $objPHPExcel->getActiveSheet()
                    ->getColumnDimension($col)
                    ->setAutoSize(true);
        }
        $worksheet->mergeCells('A1:H1');        //edited by ASHIN on 06-07-24
        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $col = 0;
        $interest= ($arr_loan_master['0']['EmployeeLoan']['intrest_rate']!=0)?$arr_loan_master['0']['EmployeeLoan']['intrest_rate']:'0';
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 2, 'Loan Amount : ' . $arr_loan_master['0']['EmployeeLoan']['loan_amount']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . 2, 'Tenure : ' . $arr_loan_master['0']['EmployeeLoan']['tenure']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . 2, '  Interest Rate : ' .$interest);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . 2, ' Started On : ' . $arr_loan_master['0']['EmployeeLoan']['emi_start_month']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . 2, ' Employee ID : ' .$arr_loan_master['0']['EmployeeInfo']['employee_id']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//added by arul
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . 2, ' Branch : ' .$arr_loan_master['0']['EmployeeInfo']['branch']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . 2, ' Department : ' .$arr_loan_master['0']['EmployeeInfo']['department']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . 2, ' Designation : ' .$arr_loan_master['0']['EmployeeInfo']['designation']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

//edited by ASHIN on 06-07-24
$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col). 3, 'Remarks : '.$arr_loan_master['0']['EmployeeLoan']['remarks']);
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 3)->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 3)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
$objPHPExcel->getActiveSheet()->mergeCells('A3:H3');
  //end      
        $rowcount = 4;

        $i = 0;

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl No');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Month');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'Creation Date');
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Opening Balance');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, ' EMI');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, ' Interest');
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
        // //$objPHPExcel->getActiveSheet()->getStyle($col + 4)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '   Principal');
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '   Amount Paid');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '   Closing Balance');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '   Monthly Status');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Monthly Status added to excel by ARUL P DAS on 14/11/2019

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '   Remarks');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Remarks added to excel by ARUL P DAS on 20/11/2019

        $col = 5;
        $j = 0;

        $rowcount = $rowcount + 1;
        foreach ($arr_loan_data as $loan) {
            if($loan['EmployeeLoanInfo']['loan_emi']=='0' && ($loan['EmployeeLoanInfo']['paid_status']=='A' || $loan['EmployeeLoanInfo']['paid_status']=='P')){
            //This is to avoid to showing removed emi fields by cause of adding additional payment. By *** ARUL P DAS on 16/11/2019
            }else{
                $j+=1;
                $col = 0;

                //The below code is directly fetched from emp_loan_info table. By **** ARUL P DAS on 15/11/2019
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $loan['EmployeeLoanInfo']['loan_month']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $loan['EmployeeLoanInfo']['opening_balance']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 4
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $loan['EmployeeLoanInfo']['loan_emi']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 5
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $loan['EmployeeLoanInfo']['interest']);
                // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                // //*****Starting of column 6
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $loan['EmployeeLoanInfo']['principle']);
                // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 7
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $loan['EmployeeLoanInfo']['amount_paid']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 8
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $loan['EmployeeLoanInfo']['closing_balance']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 8
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $loan['EmployeeLoanInfo']['remarks']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 8
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $loan['EmployeeLoanInfo']['user_remarks']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                $rowcount++;
            }
        }

         
        $objPHPExcel->getActiveSheet()->setTitle('Employee Loan Details');
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

    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_Loan.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empctcdata = new EmployeeCTCData($ctcuploadtype);
        $emp_credentials_schema = $empctcdata->getFieldHeadings('UserCredentials');
        $emp_proff_schema = $empctcdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_details_schema = $empctcdata->getFieldHeadings('EmployeeDetails');
        $emp_ctc_schema = $empctcdata->getFieldHeadings('EmployeeLoan');
        $emp_schema = array_merge($emp_credentials_schema, $emp_proff_schema, $emp_details_schema, $emp_ctc_schema);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        //Fill form with existing users 
        $emp_credentials_fields = $empctcdata->getFieldNames('UserCredentials');
        $emp_details_fields = $empctcdata->getFieldNames('EmployeeDetails');
        $emp_ctc_fields = $empctcdata->getFieldNames('EmployeeCTC');
        $emp_fields = array_merge(array_keys($emp_credentials_fields), array_keys($emp_details_fields), array_keys($emp_ctc_fields));
        // debug($cond_branch);
        //die();
        //$cond_employee= '' ; 
//        $cond = array(
//            'EmployeeDetails.status' => 1);
//        if (isset($branch) && !empty($branch)) {
//
//            $cond[] = "EmployeeDetails.branch_code= '$branch' ";
//        }
//        if (isset($employee) && !empty($employee)) {
//
//            $cond[] = "EmployeeDetails.emp_fkey= '$employee' ";
//        }
//
//        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//        $arr_empdetails = $this->EmployeeDetails->find('all', array(
//            'fields' => 'UserCredentials.user_id,concat(EmployeeDetails.first_name," ",EmployeeDetails.middile_name," ",EmployeeDetails.last_name) as Name',
//            //'fields'=>"'".implode(',',$emp_fields)."'",
//            'joins' => array(
//                array(
//                    'table' => 'user_credentials',
//                    'alias' => 'UserCredentials',
//                    'type' => 'INNER',
//                    'foreignKey' => false,
//                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
//                )
//            ),
//            'conditions' => $cond
//        ));
        $cond = '';
        if (isset($employee) && !empty($employee) && $employee != 'null') {

            $cond.= " AND  EmployeeDetails.emp_pkey= if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }


        if (isset($branch) && !empty($branch) && $branch != 'null') {

            $cond.= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }

        //debug($cond);
        // debug($cond);


        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_empdetails = $this->EmployeeDetails->query(" SELECT UserCredentials.user_id, EmployeeInfo.employee_id, EmployeeInfo.EmpName FROM emp_details AS EmployeeDetails "
                . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                . " WHERE EmployeeDetails.status = ' 1 ' and EmployeeDetails.emp_pkey not in (SELECT termination.emp_fkey FROM `termination` where termination.status=1)  "
                . " $cond ");


        //debug($arr_empdetails);
        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $rows) {
            $columnindex = 0;
            foreach ($rows as $columns) {
                foreach ($columns as $column) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $columnindex++;
                }
            }
            $rowindex++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee loan Data');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    
    public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $branch_condition = '';
        $emp_condition = '';

        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }
        $param = '';
        if(isset($arr_request_data['emp'])){
            $param = "and ed.first_name like '%".$arr_request_data['emp']."%'";
        }
//        if ($emp_fkey != '') {
//            $emp_condition = "and au.emp_fkey=$emp_fkey";
//        }
        //debug($emp_condition);

        $counts = $this->EmployeeCTC->query("select
            COUNT(*)
             from emp_details ed
                            INNER join emi_upload au on (ed.emp_pkey = au.emp_pkey)
                            INNER join employee_info  ei on (au.emp_pkey = ei.emp_pkey)
                            where au.status=1 
                  $emp_condition $param"
                . "$branch_condition"
                . "  "
                . "");
        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeCTC->query("select au.emp_pkey,
            au.*,ei.employee_id,ei.EmpName
             from emp_details ed
                            INNER join emi_upload au on (ed.emp_pkey = au.emp_pkey)
                            INNER join employee_info  ei on (au.emp_pkey = ei.emp_pkey)
                            where au.status=1 
                $emp_condition $param"
                . "$branch_condition"
                . " ORDER BY emi_upload_pkey desc "
                . " limit $limit  offset $ofst  ");
        $out = array();
        //debug($arr_att);
        foreach ($arr_att as $key => $value) {
            $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : '';
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
            $out['emp_ctc_upload_pkey'] = isset($value['au']['emi_upload_pkey']) ? $value['au']['emi_upload_pkey'] : '';
            $out['emp_fkey'] = isset($value['au']['emp_pkey']) ? $value['au']['emp_pkey'] : '';
            $out['emp_loan_balance'] = isset($value['au']['amt']) ? $value['au']['amt'] : '';
            $out['start_date_effective'] = isset($value['au']['month_year']) ? $value['au']['month_year'] : '';

            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function downloademploanformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;

        //debug($ctcuploadtype);
        //debug($employee);

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_gross.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
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
        
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
        $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
        $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Company ID");
        $worksheet->setCellValueByColumnAndRow(3, 1, "Loan Amt"); 
        $worksheet->setCellValueByColumnAndRow(4, 1, "Emi");
        $worksheet->setCellValueByColumnAndRow(5, 1, "Start Date");
        $worksheet->setCellValueByColumnAndRow(6, 1, "End Date");
        $worksheet->setCellValueByColumnAndRow(7, 1, "Upload Emi");
        $worksheet->setCellValueByColumnAndRow(24, 1, "Loan PKEY");
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(27);
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);

        $cond = '';
        if (isset($employee) && !empty($employee) && $employee != 'null') {
            $cond.= " AND  EmployeeDetails.emp_pkey= if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }
        if (isset($branch) && !empty($branch) && $branch != 'null') {
            $cond.= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_empdetails = $this->EmployeeDetails->query(" SELECT ep.emp_company_id,UserCredentials.user_id, EmployeeInfo.EmpName,elp.* "
                    . "FROM emp_details AS EmployeeDetails "
                    . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                    . " LEFT JOIN emp_loan AS elp ON (EmployeeDetails.emp_pkey = elp.emp_fkey) "
                    . " LEFT JOIN emp_proff AS ep ON (EmployeeDetails.emp_pkey = ep.emp_fkey) "
                    . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                    . " WHERE EmployeeDetails.status = ' 1 ' "
//                    . " AND ect.end_date_effective is null "
                    . " $cond ");
//            debug($arr_empdetails); die(); 
        
        $rowindex = 2;
        $columnindex = 0;
            foreach ($arr_empdetails as $value) {
                $userid = $value['UserCredentials']['user_id'];
                $empname = $value['EmployeeInfo']['EmpName'];
                $emi = $value['elp']['emi_amount'];
                $loan_amt = $value['elp']['loan_amount'];
                $start_date = $value['elp']['emi_start_month'];
                $end_date = $value['elp']['emi_end_month'];
                $loan_pkey = $value['elp']['emp_loan_pkey'];
                $emp_company_id = ' '.$value['ep']['emp_company_id'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $empname);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $emp_company_id);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowindex, $loan_amt);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowindex, $emi);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowindex, $start_date);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowindex, $end_date);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowindex, $loan_pkey);
                $rowindex++;
            }
        
        $objPHPExcel->getActiveSheet()->setTitle('Employee Emi Upload ');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    
    public function uploadandsaveempemi($ctcuploadtype = 0) {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);

        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID', 'Employee Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
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
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->LoanEmi->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');

                        foreach ($arrayempdata as $key => $row) {
                            $arr_empctc_data = array();
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            
                                $Esi = isset($row['Loan PKEY']) ? $row['Loan PKEY'] : '';
//                                $start = isset($row['PF']) ? $row['PF'] : '';
                                $UaN = isset($row['Upload Emi']) ? $row['Upload Emi'] : '';
//                                $acc_no = isset($row['ACCOUNT NUMBER']) ? $row['ACCOUNT NUMBER'] : '';
//                                $bank_name = isset($row['BANK']) ? $row['BANK'] : '';
//                                $IfsC = isset($row['IFSC']) ? $row['IFSC'] : '';
//                                $Branch = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            $emp_id = isset($row['Branch']) ? $row['Branch'] : '';
                            
                            if($Esi == ''){
                                continue;
                            }
                            if($UaN == ''){
                                continue;
                            }

                            $date = '';
                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($user_id == '') {
                                continue;
                            }
                            // debug($date)
                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            )); 
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';
                            // debug($arr_empctc_data);
                            $arr_empctc_data = array();
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
                            $arr_empctc_data['modified_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['modified_date'] = date('Y-m-d');
//                            if($bank_name != '')
//                            $arr_empctc_data['bank_name'] = $bank_name;
//                            if($Branch != '')
//                            $arr_empctc_data['branch_name'] = $Branch;
//                            if($IfsC != '')
//                            $arr_empctc_data['ifsc_code'] = $IfsC;
//                            if($start != '')
//                            $arr_empctc_data['company_pf'] = $start;
//                            if($acc_no != '')
//                            $arr_empctc_data['account_no'] = $acc_no;
//                            if($UaN != '')
                            $arr_empctc_data['loan_pkey'] = $Esi; //UAN 
//                            if($Esi != '')
                            $arr_empctc_data['amt'] = $UaN;
                            $arr_empctc_data['month_year'] = $ctcuploadtype;
                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
                            
//                            $arr_empctc_data['start_date_effective'] = date("Y-m-1",strtotime($start));

                             //debug($arr_empctc_data);
//                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
//                                $fieldValue = $row[$fieldlabel];
//                                 
//                                $arr_empctc_data[$field] = $fieldValue;
//                               // debug($arr_empctc_data);
//                            }


//                            try {
//                                debug($arr_empctc_data);
                                // die();
                                $result1 = $this->LoanEmi->saveAll($arr_empctc_data);

                                //Update salary structure for employee by uploaded CTC
                                //On 20 Sep 2016
                                //arun 15-10-2016 based on ashoakn
//                                $this->updateSalStructureDistributionFn($emp_fkey);
//                            } catch (Exception $e) {
                                //debug($e);
//                            }
                        }
                    }
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee EMI Amounts imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee EMI Amounts reviced successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts revision failed, no data found!'));
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
            
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts revision failed! '));
                exit;
            
            exit;
        }
    }
    
    public function deleteEmployeesemi($id = 0){
        $this->autoRender = false;
		  $this->LoanEmi->useDbConfig = $this->Session->read('ds');
        if($id != 0){
            $this->LoanEmi->updateAll(array('status'=>0),array('emi_upload_pkey'=>$id));
            echo json_encode(array('msg' => 'Emi  deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Store Tranfer deletion failed!'));
        }
    }

    public function getLoanBalance($emp_pkey = 0) {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_loans = $this->EmployeeDetails->query("select * from emp_loan where emp_fkey = $emp_pkey");
        $loan_info = array();
        $closing_balance = 0;
        foreach ($arr_loans as $loans) {
            $loan_pkey = isset($loans['emp_loan']['emp_loan_pkey']) ? $loans['emp_loan']['emp_loan_pkey'] : 0;
            $arr_empdetails = $this->EmployeeDetails->query("select max(emp_loan_info_pkey) loan_info_pkey from emp_loan_info where loan_pkey = $loan_pkey and amount_paid != 0");
            $loan_info_pkey = $arr_empdetails['0']['0']['loan_info_pkey'];
            //debug($loan_info_pkey);
            if ($loan_info_pkey == NULL) {
                //He Does'nt Pay any loan amount
                $balance_amount_for_loan = isset($loans['emp_loan']['loan_amount']) ? $loans['emp_loan']['loan_amount'] : 0;
                $closing_balance += $balance_amount_for_loan;
                $loan_info[] = array(
                    "LoanDetails" => $loans,
                    "BalanceOutstanding" => $balance_amount_for_loan
                );
            } else {
                //Paid
                $amount_paid = $this->EmployeeDetails->query("select closing_balance as balances from emp_loan_info where emp_loan_info_pkey = '$loan_info_pkey' ");
                $closing_balance += $amount_paid['0']['emp_loan_info']['balances'];
                $loan_info[] = array(
                    "LoanDetails" => $loans,
                    "BalanceOutstanding" => $amount_paid['0']['emp_loan_info']['balances']
                );
            }
        }
        return $loan_info;
    }

}
