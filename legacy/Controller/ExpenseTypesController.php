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
class ExpenseTypesController extends AppController {
    
    public $uses = array('ExpenseType', 'Designation', 'Allocate_expense'); //models
    
    public function index(){
        
    }


    public function expense($expense_type_pkey = 0){
	$this->ExpenseType->useDbConfig = $this->Session->read('ds');
        if($expense_type_pkey == 0){
            $title = 'Add Expense Details';
        }else{
            $title = 'Edit Expense Details';
        }
        $this->set('title', $title);
        $this->layout = NULL;
        $result = $this->ExpenseType->query("select * from expense_type where expense_type_pkey = '$expense_type_pkey'");
        $this->set('result',$result); 
    }

     public function delete($user_pkey = 0)
    {
	$this->ExpenseType->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE ; 
        if($user_pkey ==0){
        }
        if ($user_pkey != 0) {
            $this->ExpenseType->updateAll(array('status' => 0), array('expense_type_pkey' => $user_pkey));
            echo json_encode(array('msg' => 'expense deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'expense deletion failed!'));
        }
    }
        
           public function save()
            {
                $this->autoRender = FALSE;
                $this->layout = null;
                $this->ExpenseType->useDbConfig = $this->Session->read('ds');
                $arr_form_data = $this->request->data;
                   $code = $arr_form_data['expense_type_code'];
                   $name = $arr_form_data['expense_type_name']; 
                   $pkey = $arr_form_data['expense_type_pkey'];

                   $cond = " ExpenseType.expense_type_pkey != $pkey";
                   $int_expensetypecount=0;
                   $int_expensenamecount = 0;
                    //if($pkey)     
                  if(isset($pkey)) {
                if ($code !='') {
                $int_expensetypecount = $this->ExpenseType->find("count", array(
                'conditions' => array('ExpenseType.expense_type_code' => $code, 'ExpenseType.status' =>  1 ,'ExpenseType.expense_type_pkey != '=> $pkey)
                   ));
                 }
                    if ($name !='') {
                      $int_expensenamecount = $this->ExpenseType->find("count", array(
                      'conditions' => array('ExpenseType.expense_type_name' => $name, 'ExpenseType.status' => 1 ,'ExpenseType.expense_type_pkey != '=> $pkey)
                   ));
                  }

                }else{
                    $arr_form_data['creation_date'] = $time['0']['0']['time'];
                    $arr_form_data['created_by'] = $this->Session->read('user_name');
                }

             if($int_expensetypecount >0) {
                   $resp["msg"] = "Expense Code Already Exist";
                   $resp = array();
                   $resp["success"] = false; 
                   echo json_encode($resp);
                 }
                if($int_expensenamecount >0){
                     $resp["msg"] = "Expense Name Already Exist";
                    $resp =array();
                    $resp["success"] = false;
                    echo json_encode($resp);
                  }

                   if($int_expensetypecount==0 && $int_expensenamecount==0){
                    // if($int_expensenamecount==0){
                    $time=$this->ExpenseType->query("select now() as time");
                    $this->set('time',$time);
                    
                    $arr_form_data['modified_date'] = $time['0']['0']['time'];   
                    $arr_form_data['modified_by'] = $this->Session->read('user_name');
                    $arr_form_data['status']=1;
                    $arr_form_data['expense_type_code']= $code;
                    //debug ($arr_form_data);
                    $result = $this->ExpenseType->save($arr_form_data);
                    $resp = array();
                    $resp["success"] = true;
                    $resp["msg"] = "Expense Added successfully";
                    echo json_encode($resp);
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
            $where[] = "(ExpenseType.expense_type_name like '%".$arr_data['expense_type_name']."%')";
        }
       
        $result_count = $this->ExpenseType->find("count",array(
             'conditions'=>array('ExpenseType.status' => 1,$where)
         ));
       
         $fields = "ExpenseType.*";
         $result = $this->ExpenseType->find("all",array(
             'conditions'=>array('ExpenseType.status' => 1,$where) ,
                    'fields' => $fields,
                    'order'=>array($sort=>$order),
                    'limit'=>intval($limit),
                    'offset'=>intval($ofst)
         ));
        $rows = array();
        foreach ($result as $key => $val) {
            $data = array_merge($val["ExpenseType"]);
            $rows[] = $data;
        }
        $resp_data["rows"] = $rows;
        $resp_data["total"] = $result_count;
        echo json_encode($resp_data);
    }
     public function checkexpensetypenameexists($expense_type_name = 0){
        $this->autoRender = false;
        $arr_requestdata = $this->request->data;
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
      echo json_encode($arr_list);
    }
  }