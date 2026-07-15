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
App::uses('ConnectionManager', 'Model');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class ProjectIncomeController extends AppController {
    
    public $name = 'ProjectIncome';
    public $datatable;
    public $uses = array('ProjectIncome','Site','ProjectIncomePayment');//models
    
    /*b
     * Lists workstatus Here
     */
    public function index(){
        
    }
    public function form($project_income_pkey = 0) {
        $this->Site->useDbConfig = $this->Session->read('ds');
        $fields = array("site_pkey", "site_id", "site_name");
        $conditions = array("status" => 1);
        if($project_income_pkey == 0){
            $title = 'Add Project Income';
        }
        else{
            $title = 'Edit Project Income';
        }
        $this->set('title', $title);
        $this->set("arr_site", $arr_site = $this->Site->find("all", array('fields' => $fields, 'conditions' => $conditions,'order'=>'site_name' )));
        if($project_income_pkey > 0){
        $data = $this->Site->query("select site.site_pkey,site.site_pkey,site.site_name,project_income.project_income_pkey,"
        . "project_income.invoice_number,project_income.invoice_date,project_income.amount,project_income.cgst,project_income.sgst,"
        . "project_income.total,project_income.payments,project_income.balance,project_income.remarks "
        . "from project_income join site on (site_pkey = project_fkey) where project_income_pkey = '$project_income_pkey'");
        $this->set('data',$data); 
        }
    }


    public function delete($project_income_pkey = 0)
    {
	$this->ProjectIncome->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE ; 
        if($project_income_pkey ==0){
        }
        if ($project_income_pkey != 0) {
            $this->ProjectIncome->updateAll(array('status' => 0), array('project_income_pkey' => $project_income_pkey));
            echo json_encode(array('msg' => 'Project Income details deleted successfully!'));
        } else {
            echo json_encode(array('msg' => 'Project Income details deletion failed!'));
        }     
    }
        
    public function save()
        {
                $this->autoRender = FALSE;
                $this->layout = null;
                $this->ProjectIncome->useDbConfig = $this->Session->read('ds');
                $this->ProjectIncomePayment->useDbConfig = $this->Session->read('ds');
                $arr_form_data = $this->request->data;
                $invoice = $arr_form_data['invoice_number'];
                   $pkey = $arr_form_datas['project_income_pkey'] = isset($arr_form_data['project_income_pkey'])?$arr_form_data['project_income_pkey']:0;
                   $cond = " ProjectIncome.project_income_pkey != $pkey";
                   $arr_form_datas['remarks'] = "'".$arr_form_data['remarks']."'";
                   $arr_form_datas['invoice_date'] = "'".$arr_form_data['invoice_date']."'";
                   $arr_form_datas['invoice_number'] = $arr_form_data['invoice_number'];
                   $arr_form_datas['project_fkey'] = $arr_form_data['project_fkey'];
                   $arr_form_datas['amount'] = $arr_form_data['amount'];
                   if($arr_form_data['cgst'] !=''){
                   $arr_form_datas['cgst'] = $arr_form_data['cgst'];
                   }else{
                   $arr_form_datas['cgst'] = 0;    
                   }
                   if($arr_form_data['sgst'] !=''){
                   $arr_form_datas['sgst'] = $arr_form_data['sgst'];
                   }else{
                   $arr_form_datas['sgst'] = 0;    
                   }
                   $arr_form_datas['total'] = $arr_form_data['total']; 
                   $arr_form_datas['payments'] = isset($arr_form_data['payamount'])?$arr_form_data['payamount'] + $arr_form_data['payments']:0; 
                   $arr_form_datas['balance'] = $arr_form_datas['total'] - $arr_form_datas['payments'];
                   $int_count=0; 
                 
                   if ($invoice !='') {
                   $int_count = $this->ProjectIncome->find("count", array(
                   'conditions' => array('ProjectIncome.invoice_number' => $invoice, 'ProjectIncome.status' =>  1 ,'ProjectIncome.project_income_pkey != '=> $pkey)
                    ));
                   }
                   if($pkey > 0) {
                   $time=$this->ProjectIncome->query("select now() as time");
                   $arr_form_datas['modified_date'] = "'".$time['0']['0']['time']."'";   
                   $arr_form_datas['modified_by'] = "'".$this->Session->read('user_name')."'";
                   $this->ProjectIncome->updateAll($arr_form_datas, array('project_income_pkey' => $pkey));
                   $arr['project_income_fkey'] = $pkey;
                   if($arr_form_data['payamount']>0){
                   $arr['payamount'] = $arr_form_data['payamount'];
                   $arr['expensedate'] = $arr_form_data['expensedate'];
                   $arr['bal'] = $arr_form_data['balance'];
                   $arr['created_by'] = $this->Session->read('user_name');
                   $result = $this->ProjectIncomePayment->save($arr);
                   }
                   $resp["success"] = true;
                   $resp["msg"] = "Project Income updated successfully!";
                   echo json_encode($resp);
                  }else{
                   if($int_count==0){
                    $time=$this->ProjectIncome->query("select now() as time");
                    $this->set('time',$time);
                    //$arr_form_data['creation_date'] = $time['0']['0']['time'];
                    $arr_form_data['created_by'] = $this->Session->read('user_name');
                    $result = $this->ProjectIncome->save($arr_form_data);
                    $insert_id = $this->ProjectIncome->getInsertid();
                    if($arr_form_data['payments']>0){
                    $arr['project_income_fkey'] = $insert_id;
                    $arr['payamount'] = $arr_form_data['payments'];
                    $arr['expensedate'] = $arr_form_data['invoice_date'];
                    $arr['bal'] = $arr_form_data['balance'];
                    $arr['created_by'] = $this->Session->read('user_name');
                    $result = $this->ProjectIncomePayment->save($arr);
                    }
                    $resp = array();
                    $resp["success"] = true;
                    $resp["msg"] = "Project Income Added successfully";
                    echo json_encode($resp);
                   }else{
                    $resp = array();
		    $resp["msg"] = "Invoice Number Already Exist";
                    $resp["success"] = false; 
                    echo json_encode($resp);   
                   }
                  }
	}
    public function listproject()
    {	
        $this->autoRender = false ;
        $this->ProjectIncome->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
       
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'project_income_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';

        $ofst = ($page - 1) * $limit;  
        $where = ""; 
        $condition = array(); 
        if(isset($arr_data['invoice_number'])){
            $where = " and ((project_income.invoice_number like '%".$arr_data['invoice_number']."%')  or (project_income.invoice_date like '%".$arr_data['invoice_date']."%') or (project_income.total like '%".$arr_data['total']."%') or (project_income.remarks like '%".$arr_data['remarks']."%') or (site.site_name like '%".$arr_data['project']."%') or (site.site_id like '%".$arr_data['project']."%'))";
            $condition[] = " ((ProjectIncome.invoice_number like '%".$arr_data['invoice_number']."%')  or (ProjectIncome.invoice_date like '%".$arr_data['invoice_date']."%') or (ProjectIncome.total like '%".$arr_data['total']."%') or (ProjectIncome.remarks like '%".$arr_data['remarks']."%') or (Site.site_name like '%".$arr_data['project']."%') or (Site.site_id like '%".$arr_data['project']."%'))";
       }
//        $result_count = $this->ProjectIncome->find("count",array(
//             'conditions'=>array('ProjectIncome.status' => 1,$condition),
//             'joins'=>array(
//               array(
//                'table' => 'site',
//                'alias' => 'Site',
//                'type' => 'LEFT',
//                'foreignKey' => false,
//                'conditions' => array('Site.site_pkey = ProjectIncome.project_fkey')
//            )
//         )));
        $counts = $this->ProjectIncome->query(" SELECT COUNT(*) FROM project_income left join site  on (site_pkey=project_fkey) 
        where project_income.status=1 and site.status=1 $where");

        $count = $counts[0][0]['COUNT(*)'];
//         $result = $this->ProjectIncome->find("all",array(
//             'conditions'=>array('ProjectIncome.status' => 1,$where) ,
//                    'order'=>array($sort=>$order),
//                    'limit'=>intval($limit),
//                    'offset'=>intval($ofst)
//         ));
//         
//        $rows = array();  
//        foreach ($result as $key => $val) {
//            $rows[] = $val["ProjectIncome"];
//        }

        $arr_att = $this->ProjectIncome->query("SELECT project_income.*,site.site_name,site.site_id FROM project_income left join site  on (site_pkey=project_fkey) 
        where project_income.status=1 and site.status=1 $where ORDER BY project_income_pkey desc limit $limit  offset $ofst ");

        $out = array();
        foreach ($arr_att as $key => $value) {
            $id = isset($value['project_income']['project_income_pkey']) ? $value['project_income']['project_income_pkey']: '';
            $arr = $this->ProjectIncome->query("SELECT expensedate from project_income_payment "
                . "where status = 1 and project_income_fkey = $id "
                . "ORDER BY income_pkey desc limit 1 ");
            $out['project_income_pkey'] = isset($value["project_income"]["project_income_pkey"]) ? $value["project_income"]["project_income_pkey"] : '';
            $out['project'] = isset($value["site"]["site_name"]) ? $value["site"]["site_name"] . ' - ' . $value["site"]["site_id"] : '';
            $out['invoice_number'] = isset($value["project_income"]["invoice_number"]) ? $value["project_income"]["invoice_number"] : '';
            $out['invoice_date'] = isset($value["project_income"]["invoice_date"]) ? date('d-m-Y',strtotime($value['project_income']['invoice_date'])):'';
            $out['amount'] = isset($value["project_income"]["amount"]) ? $value["project_income"]["amount"] : '';
            $out['cgst'] = isset($value["project_income"]["cgst"]) ? $value["project_income"]["cgst"] : '';
            $out['sgst'] = isset($value["project_income"]["sgst"]) ? $value["project_income"]["sgst"] : '';
            $out['total'] = isset($value["project_income"]["total"]) ? $value["project_income"]["total"] : '';
            $out['payments'] = isset($value["project_income"]["payments"]) ? $value["project_income"]["payments"] : '';
            $out['balance'] = isset($value["project_income"]["balance"]) ? $value["project_income"]["balance"] : '';
            $out['remarks'] = isset($value["project_income"]["remarks"]) ? $value["project_income"]["remarks"] : '';
            $out['recieptdate'] = isset($arr['0']["project_income_payment"]["expensedate"]) ? date('d-m-Y',strtotime($arr['0']["project_income_payment"]["expensedate"])) : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;

        echo json_encode($resp_att);
    }
     public function checkexpensetypenameexists($expense_type_name = 0){
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;
        $expense_type_name   = isset($arr_requestdata['expense_type_name']) ? $arr_requestdata['expense_type_name'] : '';

        $int_expensetypecount = 0;
        if ($expense_type_name  != '') {
            $this->Vehicle->useDbConfig = $this->Session->read('ds');
            $int_expensetypecount = $this->Vehicle->find("count", array(
                'conditions' => array('Vehicle.expense_type_name ' => $expense_type_name, 'Vehicle.status' => 1,)
                    )
            );
        }
        echo $int_expensetypecount;
    }
     public function checkregnoexists($reg_number = 0,$pkey = 0) {
        $this->autoRender = false;
        $arr_requestdata = $this->request->data;
        $reg_number = isset($arr_requestdata['reg_number']) ? $arr_requestdata['reg_number'] : '';
		$pkey = isset($arr_requestdata['pkey']) ? $arr_requestdata['pkey'] : '';
		if($pkey !=''){
			$Where = "Vehicle.vehicle_master_pkey != $pkey";
		}else{
			$Where = "";
		}
        $int_regcount = 0;
        if ($reg_number != '') {
            $this->Vehicle->useDbConfig = $this->Session->read('ds');
           $int_regcount = $this->Vehicle->find("count", array(
                'conditions' => array('Vehicle.reg_number' => $reg_number, 'Vehicle.status' => 1,$Where)
                    )
            );
        }
        echo $int_regcount;
    }
    public function search(){
      $this->autoRender = false;
      $this->Vehicle->useDbConfig = $this->Session->read('ds');
      $arr_request_data = $this->request->query;
      $searchkey = $arr_request_data['model_dec'];
	  $searchkey1 = $arr_request_data['reg_number'];
      $arr_list = array();
      $arr_list[] = $this->Vehicle->find('all', array(
      'fields' => 'vehicle_master_pkey,model_dec,reg_number','Vehicle.',
      'conditions' => array(
      '(Vehicle.model_dec LIKE "%' . $searchkey . '%")' or '(Vehicle.reg_number LIKE "%' . $searchkey1 . '%")','Vehicle.status' => 1
       ),
       ));
      echo json_encode($arr_list);
    }
// 
// SELECT `Example`.* FROM `examples` AS `Example` WHERE match(`Example`.`body`) against('search text');SELECT `Example`.* FROM `examples` AS `Example` WHERE match(`Example`.`body`) against('search text');
}