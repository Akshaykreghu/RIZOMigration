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

App::uses('ConnectionManager', 'Model', 'Organization');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class SiteWorkController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'SiteWork';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'SiteWork', 'Contacts', 'SiteMaster');
    public $components = array('MasterdataManagement');

    public function index() {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Contacts->useDbConfig = $this->Session->read('ds');
        $arr_employee = $this->Contacts->find('all', array('conditions' => array('status' => 1)));
        $this->set('arr_employee', $arr_employee);
        $arr_order = array('parent_id ASC');
        $arr_parent = $this->Contacts->find("all");

        //  debug($arr_menu);

        $this->set('arr_parent', $arr_parent);
    }

    public function form($site_pkey = 0) {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_designations = $this->EmployeeDetails->query("select * from designation where status = '1' ");
        $this->set('arr_designations', $arr_designations);

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        $arr_contacts = $this->EmployeeDetails->query("select * from contacts where status = '1' ");
        $this->set('arr_contacts', $arr_contacts);
//        debug($site_pkey);
        if ($site_pkey != 0) {
            $arr_sites = $this->EmployeeDetails->query("select efsr_site.*,contacts.*,concat(emp_details.first_name,' ',ifnull(emp_details.last_name,'')) as manager from efsr_site left join emp_details on (emp_details.emp_pkey = efsr_site.superviser_pkey) left join contacts on (contacts.contact_id = efsr_site.customer_contact) where efsr_site_pkey = '$site_pkey' ");
            $this->set('arr_sites', $arr_sites);

//            $arr_sites_transscv = $this->EmployeeDetails->query("select site_transactions.*,wd.day_time_desc,wd.day_time_seq,designation.desig_code,designation.id,designation.desig_name from site_transactions
//join working_day_time_procedures wd on (wd.day_time_seq = site_transactions.day_time_seq_fkey)
//left join designation on (designation.id = site_transactions.designation_id)
//where site_fkey = '$site_pkey'  and site_transactions.status = '1'  ");
//            $this->set('arr_sites_transscv', $arr_sites_transscv);
        }
    }

    public function form2() {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_designations = $this->EmployeeDetails->query("select * from designation where status = '1' ");
        $this->set('arr_designations', $arr_designations);

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);
    }

    public function insec($emp_pkey = 0) {
        $this->SiteWork->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $arr_order = array('parent_id ASC');
        $arr_parent = $this->EmployeeMenu->query("select u.status,EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active
 from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey='$emp_pkey' and u.active='Y' where EmployeeMenu.parent_id = 0 and EmployeeMenu.active = 'Y'");
        $adminid = '0';
        $fetchUser = $this->SiteWork->query("select * from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$emp_pkey' ");
        $this->set('fetchUser', $fetchUser);
        $arr_child = $this->EmployeeMenu->query("select EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey=$emp_pkey and u.active='Y' where u.active = 'Y' and EmployeeMenu.parent_id != 0 ");
        $this->set('arr_parent', $arr_parent);
        $this->set('arr_child', $arr_child);
        $arr_useraccess = $this->SiteWork->query("select * from user_access where user_fkey = $emp_pkey and active = 'Y' ");
        $this->set('arr_useraccess', $arr_useraccess);
    }

    public function lists($param = "") {
        $this->SiteWork->useDbConfig = $this->Session->read('ds');


        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;
       // debug($arr_data);
        $page = isset($arr_data['page']) ? $arr_data['page'] : '';
        $limit = isset($arr_data['limit']) ? $arr_data['limit'] : '10';
        $offset = ($page - 1) * $limit;

        $sortcolumn = isset($arr_data['sort']) ? $arr_data['sort'] : '';

        $sortorder = isset($arr_data['order']) ? $arr_data['order'] : '';

        $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '9';
        
        $arr_useraccess = $this->SiteWork->query("select ifnull((select count(*) from efsr_site where status = '1' ),0) as counts ,contacts.first_name,efsr_site.* from efsr_site join contacts on (contacts.contact_id = efsr_site.contact_id_fkey) "
                . " where efsr_site.status = '1' ORDER BY efsr_site.efsr_site_pkey DESC limit $limit offset $offset ");
//        debug("select ifnull((select sum(emp_count) from site_transactions where site_fkey = efsr_site.efsr_site_pkey and status = '1' ),0) as counts ,contacts.first_name,efsr_site.* from efsr_site join contacts on (contacts.contact_id = efsr_site.contact_id_fkey) "
//                . " where efsr_site.status = '1' ORDER BY efsr_site.efsr_site_pkey DESC limit $limit offset $offset ");
        if ($sortcolumn != '' && $sortorder !== '') {
            $arr_order = array($sortcolumn . ' ' . $sortorder);
        } else {
            $arr_order = array('site_pkey DESC');
        }
        $totalcount = $this->SiteWork->query("SELECT count(*) as cnt FROM `efsr_site` where status = '1' ");

        //debug($arr_menu);
        $rows = array();
        //debug($arr_menu);
        foreach ($arr_useraccess as $key => $val) {

            $rows[] = array_merge($val['efsr_site'], $val['0'],$val['contacts']);
        }

        $resp_data["total"] = $totalcount['0']['0']['cnt'];
        $resp_data["rows"] = $rows;




        echo json_encode($resp_data);
    }

    public function save($emp_pkey = '', $s = '') {
        $this->autoRender = false;
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        if ($s == 'All') {
            $where = '';
            $wh = '';
        } else {
            $where = "and parent_id = $s or menu_id = '$s' ";
            $wh = "";
        }
        $par = $this->EmployeeMenu->query("select menu_id from emp_menu where parent_id = '$s' $wh ");

        if (count($par) > 0) {
            $ch = $this->EmployeeMenu->query("select menu_id from emp_menu where active = 'Y' $where ");

            foreach ($ch as $menu) {
                $this->Useraccess->useDbConfig = $this->Session->read('ds');
                $arr_form_data = array();
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = '';
                $arr_form_data['menu_id'] = '';
                $arr_form_data['active'] = '';
                $arr_form_data['status'] = '0';
                $id = $menu['emp_menu']['menu_id'];
                $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $id ");
                if (count($arr_useraccess) > 0) {

                    $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                }
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $id;
                $arr_form_data['active'] = 'Y';

                $this->Useraccess->saveAll($arr_form_data);
            }
        } else {
            $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $s ");

            if (count($arr_useraccess) > 0) {

                $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
            }
            $arr_form_data['organization_id'] = '1';
            $arr_form_data['user_fkey'] = $emp_pkey;
            $arr_form_data['menu_id'] = $s;
            $arr_form_data['active'] = 'Y';
            $arr_form_data['status'] = '1'; //set ! if no submenu

            $this->Useraccess->save($arr_form_data);
        }
        echo json_encode(array('msg' => 'Useraccess saved successfully'));
    }
    
    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;

        //debug($ctcuploadtype);
        //debug($employee);

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "Sitemaster.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
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
            $worksheet->setCellValueByColumnAndRow(0, 1, "Site Code");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Site Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "SAP ID");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Tenancy");
            $worksheet->setCellValueByColumnAndRow(4, 1, "Latitude");
            $worksheet->setCellValueByColumnAndRow(5, 1, "Longitude");
            $worksheet->setCellValueByColumnAndRow(6, 1, "Address");
            $worksheet->setCellValueByColumnAndRow(7, 1, "Remarks");
            $worksheet->setCellValueByColumnAndRow(8, 1, "GBT RTT");
            $worksheet->setCellValueByColumnAndRow(9, 1, "EB DB");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);
        
        $objPHPExcel->getActiveSheet()->setTitle('Site Master ');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempctc($ctcuploadtype = 0,$contact = 0, $supervisor = 0, $user_fkey = 0) {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);

        $ctcuploadtype = 1;
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
                            $array_mandatory_column_names = array('Contact Person Name', 'Designation');
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
//                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->SiteWork->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');

                        foreach ($arrayempdata as $key => $row) {
                            $arr_empctc_data = array();
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
//                            if ($ctcuploadtype == 1) {
                                $arr_empctc_data['site_id'] = isset($row['Site Code']) ? $row['Site Code'] : '';
                                $arr_empctc_data['site_name'] = isset($row['Site Name']) ? $row['Site Name'] : '';
                                $arr_empctc_data['address'] = isset($row['Address']) ? $row['Address'] : '';
                                $arr_empctc_data['latitude'] = isset($row['Latitude']) ? $row['Latitude'] : '';
                                $arr_empctc_data['longitude'] = isset($row['Longitude']) ? $row['Longitude'] : '';
                                $arr_empctc_data['special_remarks'] = isset($row['Remarks']) ? $row['Remarks'] : '';
                                $arr_empctc_data['customer_contact'] = $contact;
                                $arr_empctc_data['sap_site_id'] = isset($row['SAP ID']) ? $row['SAP ID'] : '';
                                $arr_empctc_data['tenancy'] = isset($row['Tenancy']) ? $row['Tenancy'] : '';
                                $arr_empctc_data['gbt_rtt'] = isset($row['GBT RTT']) ? $row['GBT RTT'] : '';
                                $arr_empctc_data['eb_dg_status'] = isset($row['EB DB']) ? $row['EB DB'] : '';
                                $arr_empctc_data['superviser_pkey'] = isset($supervisor) ? $supervisor : '';
//                                $Esi = isset($row['ESI']) ? $row['ESI'] : '';
                                $arr_empctc_data['technician_pkey'] = isset($user_fkey) ? $user_fkey : '';
//                                $arr_empctc_data['bank_branch'] = isset($row['Branch']) ? $row['Branch'] : '';
//                                
////                                $start = isset($row['PF']) ? $row['PF'] : '';
//                                $arr_empctc_data['ifsc_code'] = isset($row['IFSC Code']) ? $row['IFSC Code'] : '';
//                                $arr_empctc_data['account_no'] = isset($row['Account No']) ? $row['Account No'] : '';
//                                $arr_empctc_data['first_name'] = isset($row['Contact Person Name']) ? $row['Contact Person Name'] : '';
//                                $arr_empctc_data['c_designation'] = isset($row['Designation']) ? $row['Designation'] : '';
//                                $Branch = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            $emp_id = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            if($Esi == ''){
//                                continue;
//                            }

                            $date = '';
                            
                            
//                            $emp_fkey = isset($arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey']) ? $arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey'] : '';
                            // debug($arr_empctc_data);
//                            $arr_empctc_data = array();
//                            $arr_empctc_data['status'] = 1;
//                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
//                            $arr_empctc_data['modified_by'] = $this->Session->read('login_user_id');
//                            $arr_empctc_data['modified_date'] = date('Y-m-d');

                            
//                                debug($arr_empctc_data);
                                // die();
                                $result1 = $this->SiteWork->save($arr_empctc_data);

                        }
                    }
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Sites imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Sites successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Sites import failed, no data found!'));
                        exit;
                    
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Sites import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Sites failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Contacts import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Contacts failed! '));
                exit;
            }
            exit;
        }
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
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $emp_condition = "and emp_proff.attr1 = '$emp_pkeys' ";
        } else {
            $emp_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select * from contacts where status = 1  ORDER BY contact_id DESC ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['contacts']['contact_id'],
                'text' => $value['contacts']['first_name'] . ' ' . $value['contacts']['last_name'] . ' - ' . $value['contacts']['company_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    
    public function addDefault($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function resetDefault($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ");
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function deletemens($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function listuseraccess() {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;

        $offset = ($page - 1) * $limit;

        $sortcolumn = isset($arr_data['sort']) ? $arr_data['sort'] : '';

        $sortorder = isset($arr_data['order']) ? $arr_data['order'] : '';

        $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '9';

        $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $user_fkey and active = 'Y' ");

        //debug($arr_useraccess);
        $arr_active_menus = array();
        foreach ($arr_useraccess as $key => $val) {
            $arr_active_menus[] = $val['user_access']['menu_id'];
        }

        if ($sortcolumn != '' && $sortorder !== '') {
            $arr_order = array($sortcolumn . ' ' . $sortorder);
        } else {
            $arr_order = array('parent_id ASC');
        }
        $totalcount = $this->EmployeeMenu->find("count", array(
            'conditions' => array('EmployeeMenu.parent_id != 0')
        ));

        $arr_menu = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array(/* 'EmployeeMenu.parent_id != 0', */'EmployeeMenu.active' => 'Y'),
            'offset' => $offset,
            'limit' => $limit
        ));
        //debug($arr_menu);
        $rows = array();
        //debug($arr_menu);
        foreach ($arr_menu as $key => $val) {
            $val['EmployeeMenu']['parent'] = $val[0]['parent'];
            if (in_array($val['EmployeeMenu']['menu_id'], $arr_active_menus)) {
                $val['EmployeeMenu']['accessallow'] = 'Y';
            } else {
                $val['EmployeeMenu']['accessallow'] = 'N';
            }
            $rows[] = $val['EmployeeMenu'];
        }

        $resp_data["total"] = $totalcount;
        $resp_data["rows"] = $rows;




        echo json_encode($resp_data);
    }

    public function delete($emp_pkey = '', $s = '') {
        //   debug($emp_pkey);
        //    debug($s);
        $this->autoRender = false;
        if ($s == 'All') {
            $where = '';
            $wh = '';
        } else {
            $where = "and parent_id = $s or menu_id = '$s' ";
            $wh = "and menu_id = $s";
        }
        //    $this -> Organization -> useDbConfig = $this -> Session -> read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        $par = $this->EmployeeMenu->query("select menu_id from emp_menu where parent_id = '0' $wh ");

        if (count($par) > 0) {
            $ch = $this->EmployeeMenu->query("select menu_id from emp_menu where active = 'Y'  $where ");
            //  debug($ch);
            foreach ($ch as $menu) {
                $this->Useraccess->useDbConfig = $this->Session->read('ds');
                $arr_form_data = array();
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = '';
                $arr_form_data['menu_id'] = '';
                $arr_form_data['active'] = '';
                //    debug($menu);
                $id = $menu['emp_menu']['menu_id'];
                //   debug($id);
                $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $id ");
                //  debug($arr_useraccess);
                if (count($arr_useraccess) > 0) {

                    $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                }
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $id;
                $arr_form_data['active'] = 'N';

                $this->Useraccess->saveAll($arr_form_data);
            }
        } else {
            $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $s ");

            //  debug($arr_useraccess);
            $array_men = explode(',', $s);
            //    debug($array_men);

            if (count($arr_useraccess) > 0) {


                $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $s;
                $arr_form_data['active'] = 'N';
                $this->Useraccess->save($arr_form_data);
            }
        } echo json_encode(array('msg' => 'Useraccess saved successfully'));
    }

    public function Employee($user_pkey = 0, $DD = '') {
        $this->autoRender = false;
        //  debug($DD);
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        // $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = '#';
        //  $arr_empdashboard = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='EmpDashboard'");
// debug($adminid);
        //  debug($empid);
        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = $fetchUser['0']['Useraccess']['user_access_pkey'];
//  debug($pkey);
        if ($pkey != '') {
            $arr_useraccess_data['user_access_pkey'] = $pkey;
        }
        $arr_useraccess_data['organization_id'] = '1';
        $arr_useraccess_data['user_fkey'] = $user_pkey;
        $arr_useraccess_data['menu_id'] = $adminid;
        if ($DD == "EMPLOYEE") {
            $arr_useraccess_data['active'] = 'Y';
        } else {
            $arr_useraccess_data['active'] = 'N';
        }
        $arr_useraccess_data['status'] = '1';


        $this->Useraccess->save($arr_useraccess_data);
    }

    public function saveSite() {
        $this->autoRender = false;
        $arr_data = $this->request->data;

        $this->SiteWork->useDbConfig = $this->Session->read('ds');

//       debug($arr_data);

        $arr_form_data = array();

        if ($arr_data['site_pkey']) {
            $arr_form_data['site_pkey'] = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : '';
        }
        $arr_form_data['site_id'] = $arr_data['site_id'];
        $arr_form_data['site_name'] = $arr_data['site_name'];
        $arr_form_data['latitude'] = $arr_data['latitude'];
        $arr_form_data['longitude'] = $arr_data['longitude'];
        $arr_form_data['address'] = $arr_data['address'];
        $arr_form_data['contact_id_fkey'] = $arr_data['customer_contact'];
//        $arr_form_data['user_pkey'] = $arr_data['user_pkey'];
//        $arr_form_data['contact_name'] = $arr_data['contact_name'];
//        $arr_form_data['customer_contact'] = $arr_data['customer_contact'];
        $arr_form_data['special_remarks'] = $arr_data['special_remarks'];
       $arr_form_data['jurisdiction'] = $arr_data['jurisdiction'];
       $arr_form_data['sap_site_id'] = $arr_data['sap_site_id'];
       $arr_form_data['technician_pkey'] = $arr_data['technician_pkey'];
       $arr_form_data['gbt_rtt'] = $arr_data['gbt_rtt'];
       $arr_form_data['eb_dg_status'] = $arr_data['eb_dg_status'];
       $arr_form_data['tenancy'] = $arr_data['tenancy'];
       $arr_form_data['superviser_pkey'] = $arr_data['superviser_pkey'];
//        $arr_form_data['organization_id'] = isset($arr_data['organization_id']) ? 1 : 1;

        $this->SiteWork->save($arr_form_data);

        $site_pkey = $this->SiteWork->getLastInsertId();
        
    }

    public function admin($user_pkey = 0, $DD = '') {
        $this->autoRender = false;
        //  debug($DD);
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        // $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = '0';
        //  $arr_empdashboard = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='EmpDashboard'");
// debug($adminid);
        //  debug($empid);
        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = isset($fetchUser['0']['Useraccess']['user_access_pkey']) ? $fetchUser['0']['Useraccess']['user_access_pkey'] : '';
//  debug($pkey);
        if ($pkey != '') {
            $arr_useraccess_data['user_access_pkey'] = $pkey;
        }
        $arr_useraccess_data['organization_id'] = '1';
        $arr_useraccess_data['user_fkey'] = $user_pkey;
        $arr_useraccess_data['menu_id'] = $adminid;
        if ($DD == "ADMINS") {
            $arr_useraccess_data['active'] = 'Y';
        } else {
            $arr_useraccess_data['active'] = 'N';
        }
        $arr_useraccess_data['status'] = '1';


        $this->Useraccess->save($arr_useraccess_data);
    }

    public function get() {
        $this->autoRender = FALSE;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        //      debug($_POST['emp_id']);
        $empids = $_POST['emp_id'];
        $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = $arr_useraccess['0']['empmenu']['menu_id'];
        $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$empids' and menu_id = '$adminid' and active = 'Y' ");
        //  debug($useracess);
        $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
        if ($access == 'Y') {
            $active = 'Y';
        } else {
            $active = 'N';
        }
// debug($active);
        echo json_encode($active);
    }

    public function deleteuser($user_access_pkey = 0) {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        if ($user_access_pkey != 0) {
            $this->Useraccess->updateAll(array('status' => 0), array('user_access_pkey' => $user_access_pkey));
            echo json_encode(array('msg' => 'User deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'User deletion failed!'));
        }
    }

    public function saveuseraccess() {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $menu_id = isset($arr_data['menu_id']) ? $arr_data['menu_id'] : '';
        $user_pkey = isset($arr_data['user_pkey']) ? $arr_data['user_pkey'] : '';
        $active = isset($arr_data['active']) ? $arr_data['active'] : '';
        //  debug($arr_data);
        $arr_useraccess = $this->Useraccess->find('all', array(
            'fields' => 'user_access_pkey',
            'conditions' => array(
                'menu_id' => $menu_id,
                'user_fkey' => $user_pkey
            )
                )
        );

        if (isset($arr_useraccess[0]['Useraccess']['user_access_pkey'])) {
            //Update
            $user_access_pkey = $arr_useraccess[0]['Useraccess']['user_access_pkey'];
            $this->Useraccess->updateAll(array('active' => "'$active'"), array('user_access_pkey' => $user_access_pkey));
            echo json_encode(array('msg' => 'User access updated successfully!'));
        } else {
            //Add
            $arr_useraccess_data = array();
            $arr_useraccess_data['organization_id'] = 1;
            $arr_useraccess_data['user_fkey'] = $user_pkey;
            $arr_useraccess_data['menu_id'] = $menu_id;
            $arr_useraccess_data['active'] = $active;
            $this->Useraccess->save($arr_useraccess_data);

            echo json_encode(array('msg' => 'User access saved successfully!'));
        }
    }

//    public function get_incompleteDate() {
//        
//        $this->Useraccess->useDbConfig = $this->Session->read('ds');
//        $this->autoRender = false;
//        $site_data = $this->Useraccess->query("select min(dt) start,max(dt) end from calendar_table where dt >= (select distinct min(date_format(start_date_effective,'%Y-%m-%d')) "
//                . "from site_transactions  where site_fkey='1' and site_transactions.status = '1' )
//         and  dt<= date_format(current_date,'%Y-%m-%d') and  dt not in (select att_date  from  site_shift_close where site_fkey='1' and shift_closed_status='Y' and rec_status=1 )
//					  order by '1' ;");
//        $first = $site_data['0']['0']['start'];
//        $last = $site_data['0']['0']['end'];
//        echo json_encode(array('success' => 1, 'first'=> $first , 'last' => $last ));
//    }

//    public function pnch() {
//        $this->Useraccess->useDbConfig = $this->Session->read('ds');
//        $site_data = $this->Useraccess->query("SELECT site_pkey,site_name FROM site WHERE status = '1' ");
//
//        $this->set('site_data', $site_data);
//    }
    
//    public function get_shift($month = '',$fkey = ''){
//        $this->autoRender = false;
//        $this->Useraccess->useDbConfig = $this->Session->read('ds');
//        $site_fkey = $fkey;
//        $date_att = $month;
//        $site_data = $this->Useraccess->query("select distinct day_time_seq_fkey,day_time_desc,on_dutty1,off_dutty1,working_time1,site_transactions.start_date_effective,
//site_transactions.end_date_effective  from site_transactions join working_day_time_procedures
// on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) 
//where site_transactions.site_fkey ='$site_fkey' and site_transactions.status = '1' and site_transactions.day_time_seq_fkey not in
//(select day_time_seq_fkey from site_attendance where att_date='$date_att' and site_fkey='$site_fkey' and status=3); ");
//        
//        $array = array();
//        $branch = array();
//        $site[] = array("id" => "0", "text" => "ALL");
//        foreach ($site_data as $key => $value) {
//            $site[] = array(
//                'id' => $value['site_transactions']['day_time_seq_fkey'],
//                'text' => $value['working_day_time_procedures']['day_time_desc'] 
//            );
//        }
//        $array['items'] = $site;
//        echo json_encode($array);
//    }

//    public function load_sites($site_fkey = 0,$shift_fkey = 0) {
//        $this->Useraccess->useDbConfig = $this->Session->read('ds');
//        $shift_fkey = $shift_fkey;
//        $site_fkey = $site_fkey;
//        $site_data = $this->Useraccess->query("select distinct day_time_seq_fkey,day_time_desc,on_dutty1,off_dutty1,working_time1,designation_id,emp_count,id,desig_name from site_transactions join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) join designation on (site_transactions.designation_id = designation.id) where site_transactions.day_time_seq_fkey ='$shift_fkey' and site_transactions.site_fkey ='$site_fkey' ");
//        $this->set('site_data', $site_data);
//    }

//    public function add_site($site_fkey = 0,$shift_fkey = 0,$date = '' ) {
//        $this->Useraccess->useDbConfig = $this->Session->read('ds');
//        $arr_form_data = $this->request->data;
//        $shift_fkey = $shift_fkey;
//        $site_fkey = $site_fkey;
//        $user_id = 'DEMO12611';
//        $date = $date; //'2019-01-01';
//        $site_data = $this->Useraccess->query("select distinct `ed`.`emp_pkey` AS `emp_pkey`,concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),' - ',`ep`.`emp_company_id`, ' - ',designation.desig_code) 
//AS `EmpName`,`ep`.`emp_company_id` AS `employee_id`,`br`.`branch_name` AS `branch`,`designation`.`desig_name` AS `designation`
//,`department`.`dept_name` AS `department`,`ep`.`joining_date` AS `joining_date` from ((((`emp_proff` `ep` 
//join `emp_details` `ed` on((`ed`.`emp_pkey` = `ep`.`emp_fkey`))) left join `branches` `br` 
//on((`ed`.`branch_code` = `br`.`branch_code`))) left join `designation` on((`ep`.`designation` = `designation`.`desig_code`)))
//left join `department` on((`ep`.`emp_dept` = `department`.`dept_code`))) 
//where ed.status<>0 and ed.branch_code in (select branch_code from emp_details 
//)
//and ep.joining_date <='$date'
//and ed.emp_pkey not in (
//select emp_fkey
// from site_attendance sa ,emp_details ed ,designation ,working_day_time_procedures wd
// where att_date ='$date'  and out_time is null
// and ed.emp_pkey=sa.emp_fkey
// and designation.id = sa.designation_id 
// and wd.day_time_seq=day_time_seq_fkey) ");
//        
//        $shift_data = $this->Useraccess->query("select * from working_day_time_procedures where day_time_seq = '$shift_fkey' ");
//
//        $this->set('site_data', $site_data);
//        $this->set('shift_data', $shift_data);
//    }

//    public function data_site() {
//        $this->Useraccess->useDbConfig = $this->Session->read('ds');
//        $arr_form_data = $this->request->data;
//        $date_passed = $arr_form_data['att_date']; //'2018-01-17';
//        $site_fkey = $arr_form_data['site']; //'1';
//        $shift_id =  $arr_form_data['day_time_seq_fkey']; //'1';
//        $designation_id = $arr_form_data['desig']; //'20';
//        $arr_result = array();
//        $sql_desig = "SELECT * FROM `designation` where desig_code = '$designation_id' ";
//        $row_designation_details = $this->Useraccess->query($sql_desig);
//
//        $sql = "select distinct '$date_passed' att_date,sa.emp_fkey,
//            concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
//                AS `EmpName`,on_dutty1,off_dutty1,isnextday,'d' recordtype
//                 from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
//                where ep.emp_fkey=ed.emp_pkey
//               and att_date in (select max(att_date) from site_attendance where 
//                  site_fkey = $site_fkey
//                and day_time_seq_fkey = $shift_id
//            and designation_id = '$designation_id'
//            and att_date <'$date_passed' )
//                and  site_fkey = $site_fkey
//                and day_time_seq_fkey= $shift_id
//                and designation_id = '$designation_id'
//                and ed.emp_pkey=sa.emp_fkey
//                and designation.id = sa.designation_id 
//                and wd.day_time_seq=day_time_seq_fkey
//				and sa.emp_fkey not in
//				(select sa.emp_fkey
//				 from site_attendance sa ,emp_details ed ,designation ,working_day_time_procedures wd
//				 where att_date ='$date_passed' 
//				 and ed.emp_pkey=sa.emp_fkey
//				 and designation.desig_code = sa.designation_id 
//				 and wd.day_time_seq=day_time_seq_fkey) ";
//
//
//        $row_emp_details = $this->Useraccess->query($sql);
//
//
//        $sql2 = "select distinct att_date ,site_attendance_pkey , sa.emp_fkey,
//            concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
//            AS `EmpName`,on_dutty1,off_dutty1,isnextday,in_time,out_time,'O' recordtype
//             from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
//            where att_date ='$date_passed'
//            and ep.emp_fkey=ed.emp_pkey    
//            and  site_fkey =$site_fkey  
//            and day_time_seq_fkey= $shift_id
//            and designation_id = '$designation_id'
//            and ed.emp_pkey=sa.emp_fkey
//            and designation.id = sa.designation_id 
//            and wd.day_time_seq=day_time_seq_fkey ";
//
//        $row_emp_details2 = $this->Useraccess->query($sql2);
//
//        $arr_emps = array();
//        foreach ($row_emp_details as $key => $value) {
//            $arr_emps[] = $value['sa']['emp_fkey'];
//        }
//        $unset_array = array();
//
//        foreach ($row_emp_details2 as $key => $value) {
//            $keyFound = array_search($value['sa']['emp_fkey'], $arr_emps);
//
//            if (gettype($keyFound) == "integer") {
//                $unset_array[] = $keyFound;
//                unset($row_emp_details[$keyFound]);
//            }
//        }
//
//        $unique_unset = array_unique($unset_array);
//
//        if (!empty($unique_unset) && $unique_unset != FALSE) {
//            foreach ($unique_unset as $val) {
//                unset($row_emp_details[$val]);
//            }
//        }
//        $row_emp_details = array_values($row_emp_details);
//
//        if (!empty($row_emp_details) || !empty($row_emp_details2)) {
//
//            $arrResponse = array(
//                'success' => 1,
//                'message' => 'Your attendance logs retrieved successfully',
//                'data' => array(
//                    "first_att" => $row_emp_details,
//                    "second_att" => $row_emp_details2
//                )
//            );
//        } else {
//            $arrResponse = array(
//                'success' => 0,
//                'message' => 'No attendance logs found',
//                'data' => array()
//            );
//        }
//
//        $this->set('arrResponse', $arrResponse['data']);
//    }

//    public function mark_attendance($emp_fkey = 0) {
//        $this->autoRender = false;
//        $this->Useraccess->useDbConfig = $this->Session->read('ds');
//        $arr_form_data = $this->request->data;
//
//        $site_attendance_pkey = $arr_form_data['site_attendance_pkey'];
//
//        $site_fkey = $arr_form_data['site_fkey'];
//        $user_id = $arr_form_data['day_time_seq_fkey'];
//        $day_time_seq_fkey = $arr_form_data['day_time_seq_fkey'];
//        $designation_id = $arr_form_data['designation_id'];
//        $out_times = date("H:i:s",strtotime($arr_form_data['out_time']));
//        $in_time = date("H:i:s",strtotime($arr_form_data['out_time']));
//        $att_date = $arr_form_data['att_date'];
//        $created_by = 'Admin';
//        $creation_date = date("Y-m-d H:i:S");
//        $mobile_pkey = 0;
//        
//        $arr_response = array();
//        $arr_resp = array();
//        $success;
//        if ($site_attendance_pkey != null) {
//
//            $out_time = $att_date.' '.$out_times;
//         
//            $insert_cjeck_sql1 = "SELECT `mark_site_attendance_out_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', null , '$out_time', '$att_date')  as resps ";
//        
//            $row_emp_details = $this->Useraccess->query($insert_cjeck_sql1);
//
//            $resps = $row_emp_details[0][0]['resps'];
//            $success = 1;
//            $messagess = "success";
//            if ($resps == 'update') {
//                $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$created_by',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
//                $res_save_sql = $this->Useraccess->query($save_sql);
//            } else {
//                $success = 0;
//                $messagess = $resps;
//            }
//            $arr_resp['uploaded_time'] = $creation_date;
//            $arr_resp['site_attendance_pkey'] = $site_attendance_pkey;
//            $arr_resp['mob_pkey'] = $mobile_pkey;
//        } else {
//
//            $in_time = $att_date.' '.$in_time;
//
//            $insert_cjeck_sql1 = "SELECT `mark_site_attendance_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', '$in_time', null, '$att_date')  as resps ";
//   
//            $res_insert_cjeck_sql1_sql1 = $this->Useraccess->query($insert_cjeck_sql1);
//            $resps = $res_insert_cjeck_sql1_sql1[0][0]['resps'];
//            if ($resps == 'insert') {
//
//                $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_fkey','$att_date','$in_time','$created_by')";
//   
//                $res_sql1 = $this->Useraccess->query($save_sql1);
//
//                $success = 1;
//                $messagess = "success";
//                $new_login_auditor_pkey = $this->Useraccess->getLastInsertID();
//            } else {
//                $success = 0;
//                $messagess = $resps;
//            }
//
//            $arr_resp['uploaded_time'] = $creation_date;
//            $arr_resp['mob_pkey'] = $mobile_pkey;
//        }
//        if (!empty($arr_resp)) {
//
//            $arrResponse = array(
//                'success' => $success,
//                'message' => $messagess,
//                'data' => $arr_resp
//            );
//        } else {
//            $arrResponse = array(
//                'success' => $success,
//                'message' => $messagess,
//                'data' => array()
//            );
//        }
//        return json_encode($arrResponse);
//    }

}
