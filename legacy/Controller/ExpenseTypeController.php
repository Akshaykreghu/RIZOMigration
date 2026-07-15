<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * 
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class ExpenseTypeController extends AppController {
    
    public $uses = array('ExpenseType', 'Designation', 'Allocate_expense'); //models
    
    public function index(){
        
    }


    public function expense($expense_type_pkey = 0){
		 $this->ExpenseType->useDbConfig = $this->Session->read('ds');
        
// $exp =$expense_type_pkey;
 //debug('kkk');
         if($expense_type_pkey == 0){
            $title = 'Add Expense Details';
        }
       
        else{
            $title = 'Edit Expense Details';
        }
        $this->set('title', $title);
        $this->layout = NULL;
        $result = $this->ExpenseType->query("select * from expense_type where expense_type_pkey = '$expense_type_pkey'");
        $this->set('result',$result); 
       
        $data = $this->ExpenseType->query("select * from expense_heads");
        $this->set('arr_data',$data);
        
    }

     public function delete($user_pkey = 0)
    {
		 $this->ExpenseType->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE ; 
       //debug($user_pkey);
        if($user_pkey ==0){
      
        }
        if ($user_pkey != 0) {
            $this->ExpenseType->updateAll(array('status' => 0), array('expense_type_pkey' => $user_pkey));
            echo json_encode(array('msg' => 'expense deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'expense deletion failed!'));
           // debug($user_pkey);
        }
        
        }
        
           public function save()
            {
                $this->autoRender = FALSE;
                $this->layout = null;
                $this->ExpenseType->useDbConfig = $this->Session->read('ds');
                $arr_form_data = $this->request->data;
                   //$code = $arr_form_data['expense_type_code']; 
                   $type = $arr_form_data['expense_type'];
                   $name = $arr_form_data['expense_type_name']; 
                   $pkey = $arr_form_data['expense_type_pkey'];
                   // $pkey=isset($arr_form_data['expense_type_pkey']) ? $arr_form_data['expense_type_pkey'] : 0;

                   $cond = " ExpenseType.expense_type_pkey != $pkey";
                   $int_expensetypecount=0;
                   $int_expensenamecount = 0;
                    //if($pkey)     
                  if(isset($pkey)) {
//                 if ($code !='') {
//                 $int_expensetypecount = $this->ExpenseType->find("count", array(
//                 'conditions' => array('ExpenseType.expense_type_code' => $code, 'ExpenseType.status' =>  1 ,'ExpenseType.expense_type_pkey != '=> $pkey)
//                    ));
//                  }
                    if ($name !='') {
                      $int_expensenamecount = $this->ExpenseType->find("count", array(
                      'conditions' => array('ExpenseType.expense_type_name' => $name, 'ExpenseType.status' => 1 ,'ExpenseType.expense_type_pkey != '=> $pkey)
                   ));
                  }
                }

//              if($int_expensetypecount >0) {
//                    $resp["msg"] = "Expense Code Already Exist";
//                    $resp = array();
//                    $resp["success"] = false; 
//                    echo json_encode($resp);
//                  }
                if($int_expensenamecount >0){
                     $resp["msg"] = "Expense Name Already Exist";
                     // return false;
                    $resp =array();
                    $resp["success"] = false;
                    //  $return=0;
                    // // debug($resp);
                    
                    
                    echo json_encode($resp);
                    //debug($resp);
                  
                  }
                
                 
              


                   // if($int_expensetypecount==0 && $int_expensenamecount==0){
                    if($int_expensenamecount==0){
                    $time=$this->ExpenseType->query("select now() as time");
                    $this->set('time',$time);
                    $arr_form_data['creation_date'] = $time['0']['0']['time'];
                    $arr_form_data['created_by'] = $this->Session->read('user_name');
                    $arr_form_data['modified_date'] = $time['0']['0']['time'];   
                    $arr_form_data['modified_by'] = $this->Session->read('user_name');
                    $arr_form_data['status']=1;
                    $arr_form_data['expense_head_fkey']= $type;
                    //debug ($arr_form_data);
                    $result = $this->ExpenseType->save($arr_form_data);
                    //debug($arr_form_data);
                    $resp = array();
                    $resp["success"] = true;
                    $resp["msg"] = "Expense Added successfully";
                    echo json_encode($resp);
                    //debug($resp);
                   }
                   }
                 
                  
        
                   

        
        
    
    public function listexpense()
    {
		
        $this->autoRender = false ;
         $this->ExpenseType->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
       
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'expense_type_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';

        $ofst = ($page - 1) * $limit;  
         $where = array();
        if(isset($arr_data['expense_type_name'])){
            $where[] = "(ExpenseType.expense_type_name like '%".$arr_data['expense_type_name']."%' or ExpenseHead.expense_head_name like '%".$arr_data['expense_type_name']."%')";
        }
        $joins = array(
            array(
                'table' => 'expense_heads',
                'alias' => 'ExpenseHead',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'ExpenseHead.expense_head_pkey = ExpenseType.expense_head_fkey'
                )
            )
            ); 
        $result_count = $this->ExpenseType->find("count",array(
             'conditions'=>array('ExpenseType.status' => 1,$where),
             'joins' => $joins, 
         ));
       
         $fields = "ExpenseType.*,ExpenseHead.expense_head_name";
         $result = $this->ExpenseType->find("all",array(
             'conditions'=>array('ExpenseType.status' => 1,$where) ,
                    'fields' => $fields, 
                    'joins' => $joins, 
                    'order'=>array($sort=>$order),
                    'limit'=>intval($limit),
                    'offset'=>intval($ofst)
         ));
        
         
        $rows = array();
        foreach ($result as $key => $val) {
            $data = array_merge($val["ExpenseType"],$val["ExpenseHead"]);
            //$data = ;
            $rows[] = $data;
        }
        $resp_data["rows"] = $rows;
        $resp_data["total"] = $result_count;

//debug($resp_data);
        echo json_encode($resp_data);
    }
     public function checkexpensetypenameexists($expense_type_name = 0){
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;
        // $pkey = $arr_form_data['expense_type_pkey'];
        $expense_type_name   = isset($arr_requestdata['expense_type_name']) ? $arr_requestdata['expense_type_name'] : '';

        $int_expensetypecount = 0;
        if ($expense_type_name  != '') {
            $this->ExpenseType->useDbConfig = $this->Session->read('ds');
            $int_expensetypecount = $this->ExpenseType->find("count", array(
                'conditions' => array('ExpenseType.expense_type_name ' => $expense_type_name, 'ExpenseType.status' => 1,)
                    )
            );
        }
        echo $int_expensetypecount;
    }
     public function checkexpensetypecodeexists($expense_type_code = 0) {
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;

        $expense_type_code = isset($arr_requestdata['expense_type_code']) ? $arr_requestdata['expense_type_code'] : '';

        $int_expensetypecount = 0;
        if ($expense_type_code != '') {
            $this->ExpenseType->useDbConfig = $this->Session->read('ds');
           $int_expensetypecount = $this->ExpenseType->find("count", array(
                'conditions' => array('ExpenseType.expense_type_code' => $expense_type_code, 'ExpenseType.status' => 1,)
                    )
            );
        }
        echo $int_expensetypecount;
    }
    public function search(){
      $this->autoRender = false;
      $this->ExpenseType->useDbConfig = $this->Session->read('ds');
      $arr_request_data = $this->request->query;
      $searchkey = $arr_request_data['expense_type_name'];
      $arr_list = array();
      $arr_list[] = $this->ExpenseType->find('all', array(
      'fields' => 'expense_type_pkey,expense_type_name,expense_type_code','ExpenseType.',
      'conditions' => array(
      'ExpenseType.expense_type_name LIKE "%' . $searchkey . '%"','ExpenseType.status' => 1
       ),
       ));
     //debug($arr_list);
      echo json_encode($arr_list);
    }
    public function allocate_expense($expense_type_pkey = 0) {
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $this->set("expense_type_pkey", $expense_type_pkey);
        //By ***ARUL P DAS on 2/5/2020
        $condition = array("id not in (select designation_fkey from allocate_expense where expense_type_fkey=$expense_type_pkey and status=1)");
        $arr_designation = $this->Designation->find("all", array('conditions' => array('status' => 1, $condition)));
        $this->set("arr_designation", $arr_designation);
//        debug($arr_designation);
        //query edited by ***ARUL P DAS on 2/5/2020
        $arr_designation_allocates = $this->Designation->query("select distinct designation_fkey,designation.desig_name from allocate_expense join designation on (designation.id = allocate_expense.designation_fkey) where expense_type_fkey = '$expense_type_pkey' and allocate_expense.status = '1'");
        $this->set("arr_designation_allocates", $arr_designation_allocates);
//        debug($arr_designation_allocates);
    }

    public function save_allocate() {
        $this->autoRender = FALSE;
        $this->Allocate_expense->useDbConfig = $this->Session->read('ds');
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
//         debug($arr_form_data);

        $desigs = $arr_form_data['desigs'];
        $expense_type_pkey = $arr_form_data['expense_type_pkey'];
        $created_by = $this->Session->read('user_name');
        $resp_att = array();
        try {
            if($desigs!="ALL"){//This is to allocate specified designation to expense type
                $this->Allocate_expense->query(" insert into allocate_expense(expense_type_fkey,designation_fkey,created_by) values($expense_type_pkey,$desigs,'$created_by') ");
            }

            $condition = array("id not in (select designation_fkey from allocate_expense where expense_type_fkey=$expense_type_pkey and status=1)");
            $arr_designation = $this->Designation->find("all", array('conditions' => array('status' => 1, $condition)));
            if($desigs=="ALL"){//This is to allocate all (or rest of all) designations to expense type.This is here because to find all rest of designations.(ie,after fetching $arr_designation) By ***ARUL P DAS on 2/5/2020
                foreach ($arr_designation as $des){
                    foreach ($des as $d){
                        $this->Allocate_expense->query(" insert into allocate_expense(expense_type_fkey,designation_fkey,created_by) values($expense_type_pkey,".$d['id'].",'$created_by') ");
                    }
                }
            }

            $resp_att['success'] = 1;
            $resp_att['msg'] = "Saved Successfully ";
            $resp_att["array"]=$arr_designation;
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }

    public function remove_allocate() {
        $this->autoRender = FALSE;
        // $this->Store->useDbConfig = $this->Session->read('ds');
        $this->Allocate_expense->useDbConfig = $this->Session->read('ds');
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;

//        debug($arr_form_data);
        $desigs = $arr_form_data['desigs'];
        $expense_type_pkey = $arr_form_data['expense_type_pkey'];
        $modified_by = $this->Session->read('user_name');
        $resp_att = array();
        try {
            $this->Allocate_expense->query("update allocate_expense set status = '0', modified_by='$modified_by' where expense_type_fkey = '$expense_type_pkey' and designation_fkey = '$desigs' ");
            
            $condition = array("id not in (select designation_fkey from allocate_expense where expense_type_fkey=$expense_type_pkey and status=1)");
            $arr_designation = $this->Designation->find("all", array('conditions' => array('status' => 1, $condition)));
            
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Removed Successfully ";
            $resp_att["array"]=$arr_designation;
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }
// SELECT `Example`.* FROM `examples` AS `Example` WHERE match(`Example`.`body`) against('search text');SELECT `Example`.* FROM `examples` AS `Example` WHERE match(`Example`.`body`) against('search text');
}