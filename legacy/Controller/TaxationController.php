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
class TaxationController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Taxation';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl','UserCredentials','TaxType','TaxHead','TaxHeadDetail','EmployeeTaxTransactions');
    public $components = array('MasterdataManagement');
    
    //Get all tax types
    public function getTaxTypes() {
        $this -> autoRender = FALSE;
        $this -> TaxType -> useDbConfig = $this -> Session -> read('ds');
        $tax_type = Set::extract('/TaxType/.', $this -> TaxType -> find("all"));
        
        $resp_taxtypes = array();
        foreach ($tax_type as $key => $value) {
            $resp_taxtypes["taxtypes"][$key] = $value;
        }
        return json_encode($resp_taxtypes);
    }
    
    //Get all tax heads
    public function getTaxHeads() {
        $this -> autoRender = FALSE;
        $tax_type_fkey = isset($this->request->query['tax_type_fkey'])?$this->request->query['tax_type_fkey']:0;
        $resp_taxheads = array();
        if($tax_type_fkey){
            $this -> TaxHead -> useDbConfig = $this -> Session -> read('ds');
            $tax_head = Set::extract('/TaxHead/.', $this -> TaxHead -> find("all",array('conditions'=>array('tax_type_fkey'=>$tax_type_fkey))));
            
            foreach ($tax_head as $key => $value) {
                $resp_taxheads["taxheads"][$key] = $value;
            }
        }
        return json_encode($resp_taxheads);
    }
    
    //Get all tax types and heads
    public function getTaxHeadFields() {
        $this -> autoRender = FALSE;
        $fields = 'TaxType.*,TaxHead.*';
        $joins = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TaxHead',
                'type' => 'INNER',
                'foreignKey' => false,
                'conditions'=> array('TaxType.tax_type_pkey = TaxHead.tax_type_fkey')
            )
        );
        $conditions  =   array('TaxType.tax_status'=>1,'TaxHead.tax_active'=>'Y');
        
        $this -> TaxType -> useDbConfig = $this -> Session -> read('ds');
        $tax_headfields = $this -> TaxType -> find("all",
            array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions
            )
        );
        $resp_taxheadfields = array();
        if(!empty($tax_headfields)){
            foreach ($tax_headfields as $key => $value) {
                $taxtype_value = !empty($value['TaxType'])?$value['TaxType']:array();
                $str_taxtype = $taxtype_value['tax_type'];
                if(!empty($taxtype_value)){
                    if(!isset($resp_taxheadfields[$str_taxtype])){
                        $resp_taxheadfields[$str_taxtype] = array();
                        $resp_taxheadfields[$str_taxtype] = array(
                            'tax_type_pkey' => $taxtype_value['tax_type_pkey'],
                            'tax_desc' => $taxtype_value['tax_desc'],
                            'tax_status' => $taxtype_value['tax_status'],
                            'tax_heads' => array()
                        );
                    }
                }
                
                $taxhead_value = !empty($value['TaxHead'])?$value['TaxHead']:array();
                if(!empty($taxhead_value)){
                    $resp_taxheadfields[$str_taxtype]['tax_heads'][] = array(
                        'tax_heads_pkey' => $taxhead_value['tax_heads_pkey'],
                        'tax_type_fkey' => $taxhead_value['tax_type_fkey'],
                        'tax_name' => $taxhead_value['tax_name'],
                        'tax_type' => $taxhead_value['tax_type'],
                        'tax_details' => $taxhead_value['tax_details'],
                        'tax_active' => $taxhead_value['tax_active'],
                        'attr2' => $taxhead_value['attr2'],
                        'attr1' => $taxhead_value['attr1']
                    );         
                }    
            } 
        }
        return $resp_taxheadfields;
    }
    
    //Get all tax heads
    public function getTaxHeadDetails($tax_heads_fkey=0) {       
        $this -> autoRender = FALSE;
        $this -> TaxHeadDetail -> useDbConfig = $this -> Session -> read('ds');
        $tax_headdetails = Set::extract('/TaxHeadDetail/.', $this -> TaxHeadDetail -> find("all",array('conditions'=>array('tax_heads_fkey'=>$tax_heads_fkey))));
        return $tax_headdetails;
    }
    public function deletedoc($emp_pkey =0,$head = 0,$detail = 0,$document = 0){
        $this -> autoRender = FALSE;
        $this -> EmployeeTaxTransactions -> useDbConfig = $this -> Session -> read('ds');
        $arr_emp_transaction_all = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$emp_pkey,'tax_heads_fkey'=>$head,'tax_heads_details_fkey'=>$detail)));
        $name = $arr_emp_transaction_all['0']['EmployeeTaxTransactions']['file_name'];
       
        $doc_name = str_replace($document, '', $name); 
        $doc_name = "'$doc_name'";
        $result = $this->EmployeeTaxTransactions->updateAll(array("file_name"=>$doc_name),array('emp_fkey'=>$emp_pkey,'tax_heads_fkey'=>$head,'tax_heads_details_fkey'=>$detail));
          $message = 'Document Deleted successfully';
                 return json_encode(array('status'=>1,'taxHeadKey'=>$head,'taxHeadKeyValue'=>$detail,'emp_pkey'=>$emp_pkey,'message'=>$message));
                     
    }
    public function saveemployeetaxheads($empPkey = 0){
        $message = 'Something wrong happened';
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $user_group = $this -> Session -> read("user_group");
        $arr_form_data  =   $this->request->data;
        $empPkey = isset($arr_form_data['emp_pkey'])?$arr_form_data['emp_pkey']:0;
        //edited by megha fin_year for individual employee 
        $years= $this->EmployeeTaxTransactions->query("select fin_year from fin_year left join branches on (branches.branch_code = fin_year.branch_code) left join emp_details on (emp_details.branch_code =fin_year.branch_code) where Year_status = 'OPEN' and vattr1 = 1 and is_current_finyear = 'Y' and fin_year.status = '1' and emp_pkey=$empPkey");
        $year = $years['0']['fin_year']['fin_year'];
        $this->autoRender   =   FALSE;
        $user = $this -> Session -> read('login_user_id');
        $users = "'$user'";
        $this->layout   =   null;
       
        unset($arr_form_data['emp_pkey']);
        $checkall = isset($arr_form_data['checkall_0'])?$arr_form_data['checkall_0']:0;
        $this -> EmployeeTaxTransactions -> useDbConfig = $this -> Session -> read('ds');
        $temp_newkey=0;//This is to compare checked checkbox key with corresponding elements key. by ***ARUL P DAS on 25/1/2020
        $checkall = isset($arr_form_data['checkall_0'])?$arr_form_data['checkall_0']:'0';
        $uncheckall=isset($arr_form_data['uncheckall_0'])?$arr_form_data['uncheckall_0']:'0';
        foreach ($arr_form_data as $key => $value) {
            
            $arr = explode('_', $key);
            $newkey = array_pop($arr);
            
         if($newkey  != '0'){
            
            $arr_emp_transaction = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$newkey,'tax_heads_details_fkey'=>0)));
            if(count($arr_emp_transaction)>0){
                //Update
                $value = ($value!='')?$value:0;
                //edited by megha on 18/08/2019 isset condition
                //$old_value = $arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value'];
                $old_value = isset($arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value'])?$arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value']:0;
                //$this->EmployeeTaxTransactions->updateAll(array("tax_value"=>$value,"modified_by" => $users,"fin_year"=>$year,"last_value" => $old_value),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$newkey,'tax_heads_details_fkey'=>0));//On 28 June 2016
                if($user_group == 1){ //added by megha for locking action only for admin
                if($arr[0]=='check'){//This is to check is the current entry is a checkbox. By ***ARUL P DAS on 25/1/2020
                    $this->EmployeeTaxTransactions->query("UPDATE `emp_tax_transactions` SET `locked` = 'Y'  WHERE `emp_fkey` = ".$empPkey." AND `tax_heads_fkey` = ".$newkey." AND `tax_heads_details_fkey` = 0");
                    //This query is to update the corresponding tax head as locked. by ***ARUL P DAS on 25/1/2020
                }else{
                    if ($newkey==$temp_newkey) {//This is to check whether checked element continues or not.That is, a checked element key will repeat ones again. By ***ARUL P DAS on 25/1/2020
                        $this->EmployeeTaxTransactions->updateAll(array("tax_value"=>$value,"modified_by" => $users,"fin_year"=>$year,"last_value" => $old_value),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$newkey,'tax_heads_details_fkey'=>0));
                    }else{
                        $this->EmployeeTaxTransactions->updateAll(array("tax_value"=>$value,"modified_by" => $users,"fin_year"=>$year,"last_value" => $old_value, "locked"=>"'N'"),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$newkey,'tax_heads_details_fkey'=>0));
                    }
                    //On 28 June 2016
                }
                }else{
                     $this->EmployeeTaxTransactions->updateAll(array("tax_value"=>$value,"modified_by" => $users,"fin_year"=>$year,"last_value" => $old_value),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$newkey,'tax_heads_details_fkey'=>0));//On 28 June 2016
                }
            }else{
                //Insert
                $arr_data = array();
                $arr_data['emp_tax_tran_id'] = 0;
                $arr_data['emp_fkey'] = $empPkey;
                $arr_data['tax_heads_fkey'] = $newkey;
                $arr_data['tax_heads_details_fkey'] = 0;
                $arr_data['tax_value'] = ($value!='')?$value:0;
                $arr_data['created_by'] = $user;
                $arr_data['fin_year'] = $year;
                if($user_group == 1){ 
                if($arr[0]=='check'){//This is to check is the current entry is a checkbox. By ***ARUL P DAS on 25/1/2020
                    $arr_data['locked'] = 'Y';
                }else{
                    $arr_data['locked'] = 'N';
                }
                }
                $this->EmployeeTaxTransactions->save($arr_data);
            }
              $temp_newkey=$newkey;
         }
        }
        if($user_group == 1){ 
        if($checkall == 'on'){
            $this->EmployeeTaxTransactions->query("UPDATE `emp_tax_transactions` SET `locked` = 'Y'  WHERE `emp_fkey` = ".$empPkey);
        }
        if($uncheckall=='on'){
            $this->EmployeeTaxTransactions->query("UPDATE `emp_tax_transactions` SET `locked` = 'N'  WHERE `emp_fkey` = ".$empPkey);
        }
        }
        $message = 'Details saved successfully';
        return json_encode(array('success'=>TRUE,'message'=>$message));
       
    }
    public function uploadFile(){
      // debug($_FILES);
         $this->autoRender   =   FALSE;
        $this->layout   =   null;
        $arr_form_data  =   $this->request->data;
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
       // debug($arr_form_data); 
        $empPkey = isset($arr_form_data['emp_pkey'])?$arr_form_data['emp_pkey']:0;
        $taxHeadKey = isset($arr_form_data['tax_heads_fkey'])?$arr_form_data['tax_heads_fkey']:0;
        $tax_heads_detail = isset($arr_form_data['tax_heads_detail'])?$arr_form_data['tax_heads_detail']:0;
        $locked_data = $this->EmployeeTaxTransactions->query("select locked from emp_tax_transactions where emp_fkey =$empPkey and tax_heads_fkey= $taxHeadKey and tax_heads_details_fkey = 0");
      //  debug($this->EmployeeTaxTransactions->find('all',array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>'0')));
        $lock = $locked_data['0']['emp_tax_transactions']['locked'];
        if($lock =='Y'){
          $message = 'Details cannot edit, Admin has locked the Tax Heads..';
        return json_encode(array('success'=>False,'taxHeadKey'=>$taxHeadKey,'taxHeadKeyValue'=>0,'message'=>$message));
        }else{
          
          
          try{
         // foreach ($_FILES as $key => $val) {
             $dirsep = "/";
           //$fil = explode('/', $key);
           // $newkey = array_pop($fil);
           // debug($newkey);
             $companycode = $this->Session->read('company_code');
             $user_group = $this -> Session -> read("user_group");
            
             $companycode = strtoupper($companycode);
             $type = $_FILES['taxfile']['type'];
                $cwd_path = getcwd() . $dirsep;
                //debug($cwd_path);
                $file_webroot_path = "files" . $dirsep . "taxdocuments" . $dirsep . $companycode . $dirsep;
               // debug($file_webroot_path);
                if (!file_exists($cwd_path . $file_webroot_path)) {
                    mkdir($cwd_path . $file_webroot_path, 0755, TRUE);
                }
                if (!isset($_FILES['taxfile']['error']) || is_array($_FILES['taxfile']['error'])) {
                    throw new RuntimeException('Invalid parameters.');
                }

                // Check $_FILES['upfile']['error'] value.
                switch ($_FILES['taxfile']['error']) {
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
                try{
                if ($_FILES['taxfile']['size'] > 2000000) {
                    throw new RuntimeException('Exceeded filesize limit.');
                }
                } catch (Exception $ex) {
                                $message = 'Document not Uploaded. Exceeded filesize limit.';
                               // if(substr($ex->getMessage(),0,15) == "SQLSTATE[23000]")$message = 'User Already Exists With the same COMPANYID try again, or contact the administartor';
                                return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(),'message' => $message));
                           }

                // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                // Check MIME Type by yourself.
                 try{
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (false === $ext = array_search(
                        $finfo->file($_FILES['taxfile']['tmp_name']), array(
                    'jpg' => 'image/jpg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf'=> 'application/pdf',
                    'xls' => 'text/xls',
                    'xlxs' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'doc' => 'application/msword',
                        ), true
                        )) {
                    throw new RuntimeException('Invalid file format.');
                }
                } catch (Exception $ex) {
                                $message = 'Document not Uploaded. Invalid file format.';
                               // if(substr($ex->getMessage(),0,15) == "SQLSTATE[23000]")$message = 'User Already Exists With the same COMPANYID try again, or contact the administartor';
                                return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(),'message' => $message));
                }
              
                 // You should name it uniquely.
                // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                // On this example, obtain safe unique name from its binary data.
                //$filename = sprintf('img/companylogos/' . $this->Session->read('company_code') . '/%s.%s', sha1_file($_FILES['companylogofile']['tmp_name']), $ext);
               //$filename = sprintf('%s.%s', sha1_file($_FILES['taxfile']['tmp_name']), $ext);
                 //$filename = sprintf('%s.%s', sha1_file($_FILES['taxfile']['tmp_name']), $ext);
                
               $arr_emp_transaction = $this->EmployeeTaxTransactions->find('count',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$tax_heads_detail,'file_name >'=>0)));
               $arr_emp_transaction_all = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$tax_heads_detail)));
               $count = substr_count($arr_emp_transaction_all['0']['EmployeeTaxTransactions']['file_name'],",");
               $doccount = $count + 1; 
               if($user_group == 1){
                $filename = 'admin_'.$empPkey.'_'.$taxHeadKey.'_'.$tax_heads_detail.'_'.$doccount.'.'.$ext;
               }else{
                $filename = 'employee_'.$empPkey.'_'.$taxHeadKey.'_'.$tax_heads_detail.'_'.$doccount.'.'.$ext;   
               }
            //  debug($filename);
                //if(move_uploaded_file($_FILES["taxfile"]["tmp_name"], $cwd_path . $file_webroot_path . $filename)){
             // echo "Stored in: " . $cwd_path . $file_webroot_path . $filename ;
                if (!move_uploaded_file($_FILES['taxfile']['tmp_name'], $cwd_path . $file_webroot_path . $filename)) {
                    throw new RuntimeException('Failed to move uploaded file.');
                }
                
                
           } catch (Exception $ex) {
           $message = 'Document not Uploaded. Something went wrong.';
             // if(substr($ex->getMessage(),0,15) == "SQLSTATE[23000]")$message = 'User Already Exists With the same COMPANYID try again, or contact the administartor';
            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(),'message' => $message));
            }
                 
                //debug($arr_emp_transaction_all);
                
               // if(!empty($arr_emp_transaction_all)){
            //edited by sinsiya 0n 07-03-2025
                if($arr_emp_transaction >0){  
                    $file_name = $arr_emp_transaction_all['0']['EmployeeTaxTransactions']['file_name'];
                      $filename = "'$file_name,$filename'";
                      $file_type = $arr_emp_transaction_all['0']['EmployeeTaxTransactions']['file_type'];
                      $type = "'$file_type,$type'";
                       $result = $this->EmployeeTaxTransactions->updateAll(array("file_name"=>$filename,"file_type" => $type),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$tax_heads_detail));
               
                }
                else if($arr_emp_transaction == 0 && empty($arr_emp_transaction_all)) {
                  $filename = "";
                 $type = "";  
                }else{
                    $filename = "'$filename'";
                 $type = "'$type'";   
                  $result = $this->EmployeeTaxTransactions->updateAll(array("file_name"=>$filename,"file_type" => $type),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$tax_heads_detail));
               
                }
               // }else{
//                    if(!empty($arr_emp_transaction_all)){
//                $arr_emp_transactions = $this->EmployeeTaxTransactions->find('count',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>0,'file_name >'=>0)));
//                $arr_emp_transactions_all = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>0,'file_name >'=>0)));
//              
//               // debug($filename);
//                if($arr_emp_transactions == 1){
//                    $file_name1 = $arr_emp_transactions_all['0']['EmployeeTaxTransactions']['file_name'];
//                    if($file_name1){
//                      $filename1 = "'$file_name1,$filename'";
//                    }
//                      $file_type1 = $arr_emp_transactions_all['0']['EmployeeTaxTransactions']['file_type'];
//                      if($file_type1){ 
//                      $type1 = "'$file_type1,$type'";
//                      }
//                }else{
//                  $filename1 = "'$filename'";
//                 $type1 = "'$type'";  
//                }
//                 $result = $this->EmployeeTaxTransactions->updateAll(array("file_name"=>$filename1,"file_type" => $type1),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>0));    
//                }
//  debug($result);
                 $message = 'Document saved successfully';
                 return json_encode(array('status'=>1,'taxHeadKey'=>$taxHeadKey,'taxHeadKeyValue'=>$tax_heads_detail,'message'=>$message));
                //$arr_form_data["logo"] = $file_webroot_path . $filename;
               // $this->Session->write('company_logo', $file_webroot_path . $filename);
      }
         //}
    }
     public function savetaxdetail($empPkey=0,$taxHeadKey=0,$taxHeadDetail=0,$taxvalue=0){
        $message = 'Something wrong happened';
        $this->autoRender   =   FALSE;
        $this->layout   =   null;
        
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $years= $this->EmployeeTaxTransactions->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 1 and is_current_finyear = 'Y' and status = '1'");
             $year = $years['0']['fin_year']['fin_year'];
             $user = $this -> Session -> read('login_user_id');
             $users = "'$user'";
        
        $locked_data = $this->EmployeeTaxTransactions->query("select locked from emp_tax_transactions where emp_fkey =$empPkey and tax_heads_fkey= $taxHeadKey and tax_heads_details_fkey = 0");
        $lock = $locked_data['0']['emp_tax_transactions']['locked'];
      if($lock =='Y'){
          $message = 'Details cannot edit, Admin has locked the Tax Heads..';
      return json_encode(array('success'=>False,'taxHeadKey'=>$taxHeadKey,'taxHeadKeyValue'=>0,'message'=>$message));
      }else{
        $this -> EmployeeTaxTransactions -> useDbConfig = $this -> Session -> read('ds');
        $arr_emp_transaction = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$taxHeadDetail)));
            if(count($arr_emp_transaction)>0){
                //Update
                $taxvalue = ($taxvalue!='')?$taxvalue:0;
                $old_value = isset($arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value'])?$arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value']:0;
                $this->EmployeeTaxTransactions->updateAll(array("tax_value"=>$taxvalue,"modified_by" => $users,"fin_year"=>$year,"last_value" => $old_value),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$taxHeadDetail));
            }else{
                //Insert
                $arr_data = array();
                $arr_data['emp_tax_tran_id'] = 0;
                $arr_data['emp_fkey'] = $empPkey;
                $arr_data['tax_heads_fkey'] = $taxHeadKey;
                $arr_data['tax_heads_details_fkey'] = $taxHeadDetail;
                $arr_data['tax_value'] = ($taxvalue!='')?$taxvalue:0;
                $arr_data['created_by'] = $user;
                $arr_data['fin_year'] = $year;
                $this->EmployeeTaxTransactions->save($arr_data);
            }
           
        $message = 'Details saved successfully';
        return json_encode(array('success'=>TRUE,'taxHeadKey'=>$taxHeadKey,'taxHeadKeyValue'=>$taxHeadDetail,'message'=>$message));
      }
    }
    public function saveemployeetaxheaddetails($empPkey=0,$taxHeadKey=0){
    
     
        $message = 'Something wrong happened';
        $this->autoRender   =   FALSE;
        $this->layout   =   null;
        $arr_form_data  =   $this->request->data;
        
       //  debug($arr_form_data);
        $empPkey = isset($arr_form_data['emp_pkey'])?$arr_form_data['emp_pkey']:0;
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $years = $this->EmployeeTaxTransactions->query("select fin_year from fin_year where Year_status = 'OPEN' and branch_code in (select branch_code from emp_details where emp_pkey =$empPkey) and vattr1 = 1 and is_current_finyear = 'Y' and status = '1'");
             $year = $years['0']['fin_year']['fin_year'];
             $user = $this -> Session -> read('login_user_id');
        $users = "'$user'";
        
        $taxHeadKey = isset($arr_form_data['tax_heads_fkey'])?$arr_form_data['tax_heads_fkey']:0;
        unset($arr_form_data['emp_pkey']);
        unset($arr_form_data['tax_heads_fkey']);
        //$empPkey = "'$empPkey'";
        //$taxHeadKey = "'$taxHeadKey'";
        $locked_data = $this->EmployeeTaxTransactions->query("select locked from emp_tax_transactions where emp_fkey =$empPkey and tax_heads_fkey= $taxHeadKey and tax_heads_details_fkey = 0");
      //  debug($this->EmployeeTaxTransactions->find('all',array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>'0')));
        $lock = isset($locked_data['0']['emp_tax_transactions']['locked'])?$locked_data['0']['emp_tax_transactions']['locked']:'';
      if($lock =='Y'){
          $message = 'Details cannot edit, Admin has locked the Tax Heads..';
      return json_encode(array('success'=>False,'taxHeadKey'=>$taxHeadKey,'taxHeadKeyValue'=>0,'message'=>$message));
      }else{
        $this -> EmployeeTaxTransactions -> useDbConfig = $this -> Session -> read('ds');
        $taxHeadKeyValue = 0;
        foreach ($arr_form_data as $key => $value) {
            if($key =='lockeddata'){
                continue;
            }
            $arr = explode('_', $key);
            $newkey = array_pop($arr);
           // debug($newkey);
            $arr_emp_transaction = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$newkey,'fin_year'=>$year)));
            if(count($arr_emp_transaction)>0){
                //Update
                $value = ($value!='')?$value:0;
              ////edited by megha on 17/08/2019 isset condition
                //$old_value = $arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value'];
             $old_value = isset($arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value'])?$arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value']:0;
                 $this->EmployeeTaxTransactions->updateAll(array("tax_value"=>$value,"modified_by" => $users,"fin_year"=>$year,"last_value" => $old_value),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$newkey,'fin_year'=>$year));
             //added by megha for deleting document when saving zero value 19/02/2020
                if($value <= 0){
                $result = $this->EmployeeTaxTransactions->updateAll(array("file_name"=>"Null","file_type" => "Null"),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>$newkey,'fin_year'=>$year));
                }
                //end 19/02/2020
                 
            }else{
                //Insert
                $arr_data = array();
                $arr_data['emp_tax_tran_id'] = 0;
                $arr_data['emp_fkey'] = $empPkey;
                $arr_data['tax_heads_fkey'] = $taxHeadKey;
                $arr_data['tax_heads_details_fkey'] = $newkey;
                $arr_data['tax_value'] = ($value!='')?$value:0;
                $arr_data['created_by'] = $user;
                $arr_data['fin_year'] = $year;
                $this->EmployeeTaxTransactions->save($arr_data);
            }
            $taxHeadKeyValue += $value;
             //edited by megha on 31/07/2019 not saving taxsalaryhead total in tax deduction form 
            $arr_emp_transactions = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>0,'fin_year'=>$year)));
            if(count($arr_emp_transactions)>0){
                $tax_value = ($value!='')?$taxHeadKeyValue:0;
                //edited by megha on 18/09/2019 saving taxsalary info
                $old_value = isset($arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value'])?$arr_emp_transaction['0']['EmployeeTaxTransactions']['tax_value']:0;
                $this->EmployeeTaxTransactions->updateAll(array("tax_value"=>$tax_value,"modified_by" => $users,"fin_year"=>$year,"last_value" => $old_value),array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey'=>'0','fin_year'=>$year));
               
            }else{
                $arr_datas = array();
                $arr_datas['emp_fkey'] = $empPkey;
                $arr_datas['tax_heads_fkey'] = $taxHeadKey;
                $arr_datas['tax_heads_details_fkey'] = 0;
                $arr_datas['tax_value'] = ($value!='')?$taxHeadKeyValue:0;
                $arr_datas['created_by'] = $user;
                $arr_datas['fin_year'] = $year;
                $this->EmployeeTaxTransactions->save($arr_datas);
            }
                //end not saving taxsalaryhead total in tax deduction form
        }
        $message = 'Details saved successfully';
        return json_encode(array('success'=>TRUE,'taxHeadKey'=>$taxHeadKey,'taxHeadKeyValue'=>$taxHeadKeyValue,'message'=>$message));
      }
    }

    public function loadEmpTaxationDetails($empPkey = 0){        
            $this->layout   =   null;
            $this->autoRender   =   FALSE;
            
            $return_arr_emp_transaction = array();
            if(isset($empPkey) && $empPkey != 0 && $empPkey != ''){
                    $arr_emp_transaction   =   array();
                    $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
                    $arr_emp_transaction = Set::extract('/EmployeeTaxTransactions/.', $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_details_fkey'=>0))));
                    foreach($arr_emp_transaction as $emptransaction){
                        $key = /*'tax_head_'.*/$emptransaction['tax_heads_fkey'];
                       // $return_arr_emp_transaction[$key]=$emptransaction['tax_value'];
                        $return_arr_emp_transaction[$key]['tax_value']=$emptransaction['tax_value'];
                        $return_arr_emp_transaction[$key]['locked']=$emptransaction['locked'];
                    }
                    //return json_encode(array('success'=>true,'data'=>$return_arr_emp_transaction));
            }
            return $return_arr_emp_transaction;
    }

    public function loadEmpTaxHeadDetails($empPkey = 0, $taxHeadKey = 0)
    {
        $this->layout   =   null;
        $this->autoRender   =   FALSE;

        $return_arr_emp_transaction = array();
        if (isset($empPkey) && $empPkey != 0 && $empPkey != '' && isset($taxHeadKey) && $taxHeadKey != 0 && $taxHeadKey != '') {
            $arr_emp_transaction   =   array();
            //Edited by Akshay on 5-9-2024
            $arr_fin_year = $this->EmployeeTaxTransactions->query("SELECT fin_year.fin_year FROM emp_details ed
                                                                        LEFT JOIN fin_year ON (fin_year.branch_code = ed.branch_code AND fin_year.Year_status = 'OPEN' AND fin_year.is_current_finyear = 'Y' AND fin_year.vattr1 = '1')
                                                                        WHERE ed.emp_pkey = '$empPkey'
                                                                            ");
            $fin_year = isset($arr_fin_year[0]['fin_year']['fin_year'])? $arr_fin_year[0]['fin_year']['fin_year']:'';
            //End

            $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
            //Edited by Akshay on 5-9-2024
            if($fin_year == ''){
                $arr_emp_transaction = Set::extract('/EmployeeTaxTransactions/.', $this->EmployeeTaxTransactions->find('all', array('conditions' => array('emp_fkey' => $empPkey, 'tax_heads_fkey' => $taxHeadKey, 'tax_heads_details_fkey != 0'))));
            }else{
                $arr_emp_transaction = Set::extract('/EmployeeTaxTransactions/.', $this->EmployeeTaxTransactions->find('all', array('conditions' => array('emp_fkey' => $empPkey, 'tax_heads_fkey' => $taxHeadKey, 'tax_heads_details_fkey != 0', 'fin_year' => $fin_year))));
            }
            //End
            foreach ($arr_emp_transaction as $emptransaction) {
                $key = /*'tax_head_detail_'.*/ $emptransaction['tax_heads_details_fkey'];
                $return_arr_emp_transaction[$key] = $emptransaction['tax_value'];
            }
            //return json_encode(array('success'=>true,'data'=>$return_arr_emp_transaction));
        } else {
            //return json_encode(array('success'=>false,'data'=>array()));
        }
        return $return_arr_emp_transaction;
    }
	  public function loadEmpTaxHeadDocuments($empPkey = 0,$taxHeadKey = 0){        
            $this->layout   =   null;
            $this->autoRender   =   FALSE;
            
            $return_arr_emp_transaction = array();
            if(isset($empPkey) && $empPkey != 0 && $empPkey != '' && isset($taxHeadKey) && $taxHeadKey != 0 && $taxHeadKey != ''){
                    $arr_emp_transaction   =   array();
                    
                    $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
                    //$arr_emp_transaction = Set::extract('/EmployeeTaxTransactions/.', $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey,'tax_heads_details_fkey != 0'))));
                    $arr_emp_transaction = Set::extract('/EmployeeTaxTransactions/.', $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$empPkey,'tax_heads_fkey'=>$taxHeadKey))));
                    
                    foreach($arr_emp_transaction as $emptransaction){
                        $key = /*'tax_head_detail_'.*/$emptransaction['tax_heads_details_fkey'];
                        $return_arr_emp_transaction[$key]=$emptransaction['file_name'];
                    }
                    //return json_encode(array('success'=>true,'data'=>$return_arr_emp_transaction));
            }
            else{
                //return json_encode(array('success'=>false,'data'=>array()));
            }
            return $return_arr_emp_transaction;
    }
    //Get PF Tax Value
    public function getPFTaxValue($emp_fkey=0) {       
        $this -> autoRender = FALSE;
		$this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
		//debug($this->EmployeeTaxTransactions->query('SELECT find_pf_tax_cal_fn("' . $emp_fkey . '")'));
		$arr = $this->EmployeeTaxTransactions->query('SELECT find_pf_tax_cal_fn("' . $emp_fkey . '") AS VAL');
		return isset($arr[0][0]['VAL'])?$arr[0][0]['VAL']:'';
    }
}