<?php
/* include autoloader */
require_once '../Vendor/dompdf/autoload.inc.php';

/* reference the Dompdf namespace */
use Dompdf\Dompdf;
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
App::import('Vendor','Dompdf');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */


class SurveyController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Survey';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('SurveyType','SurveyCategory','OptionItems','OptionItemsValues','EfsrTickets','EfsrSite','EfsrEquipmentsMaster','EmpDetails','EquipmentType');
    public $components = array('MasterdataManagement');

    /*** Survey Type Master ***/
    public function index() {

    }

    public function form($type_pkey=''){
        $this->EquipmentType->useDbConfig = $this->Session->read('ds');
        $equipments = $this->EquipmentType->query("SELECT equipment_type_pkey, CONCAT(equipment_code,' | ',equipment_name) as equipment_name FROM `equipment_type` where status = 1"); 
        $this->set('equipments',$equipments);
        $this->set('equipment_type_fkey','');
        if($type_pkey != ''){
            $this->SurveyType->useDbConfig = $this->Session->read('ds');
            $result = $this->SurveyType->query('select type_pkey,type_code ,type_name,equipment_type_fkey from survey_type where  type_pkey='.$type_pkey.' and status = 1 ');  

            if (isset($result[0]) && isset($result[0]['survey_type'])){
                $this->set('type_code',(isset($result[0]['survey_type']['type_code']) ? $result[0]['survey_type']['type_code'] : "" ));
                $this->set('type_name',(isset($result[0]['survey_type']['type_name']) ? $result[0]['survey_type']['type_name'] : "" ));
                $this->set('equipment_type_fkey',(isset($result[0]['survey_type']['equipment_type_fkey']) ? $result[0]['survey_type']['equipment_type_fkey'] : "" ));
            }
        }
        $this->set('type_pkey',$type_pkey);
    }
    public function surveyTypesList($asArray = false){
        $this->layout = '';
        $this->SurveyType->useDbConfig = $this->Session->read('ds');
        $result = $this->SurveyType->query("select type_pkey,type_code ,type_name,CONCAT(equipment_code, ' | ',equipment_name) as equipment_name from survey_type as st LEFT JOIN equipment_type as et on (et.equipment_type_pkey = st.equipment_type_fkey and et.status = 1) where st.status = 1");

        if ($asArray == true)
            return $result;
        $this->autoRender = false;
        $survey_types = array();
        foreach ($result as $key => $val) {
             $survey_types[] =  array_merge($val['st'],$val[0]) ;
        }
        echo json_encode($survey_types);
    }
    public function getSurveyTypes($asArray = false){
        $this->layout = '';
        $this->SurveyType->useDbConfig = $this->Session->read('ds');
        $result = $this->SurveyType->query('select type_pkey,type_code ,type_name from survey_type where status = 1');

        if ($asArray == true)
            return $result;
        $this->autoRender = false;
        $survey_types = array();
        foreach ($result as $key => $val) {
             $survey_types[] =  $val['survey_type'];
        }
        echo json_encode($survey_types);
    }
    public function saveSurveyType(){
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->SurveyType->useDbConfig = $this->Session->read('ds');
        $data = $_POST;
        $action = (isset($data['type_pkey']) && (!empty($data['type_pkey'])))?'edit':'add';

        $duplicate_condition = ($action == 'edit')?" and type_pkey != ".$data['type_pkey']."" :"";

        $duplicateData = $this->SurveyType->query("SELECT * FROM survey_type WHERE (type_code = '".$data['type_code']."' or  type_name  = '".$data['type_name']."' ) and status = 1 ".$duplicate_condition."");
         if (!empty($duplicateData)){
            $error = array('type_code'=>false,'type_name'=>false);
            foreach ($duplicateData as $key => $value) {
                if(array_search($data['type_code'], array_column($value, 'type_code')) !== false) {
                    $error['type_code'] = true;
                } 
                if(array_search($data['type_name'], array_column($value, 'type_name')) !== false) {
                    $error['type_name'] = true;
                }
            } 
             echo json_encode(array('msg'=>'SurveyType  already exists.','status'=>false,'err'=>$error));
        }else{
            if ($action == 'add'){
                $data['created_by'] = $loginUser;
                $data['creation_date'] = date('Y-m-d H:i:s');
                $this->SurveyType->save($data);
            }else{
                $data['modified_by'] = $loginUser;
                $data['modification_date'] = date('Y-m-d H:i:s');
                $this->SurveyType->save($data,true,array('type_code','type_name','equipment_type_fkey','modified_by','modification_date'));

            } 
            echo json_encode(array('msg' => 'SurveyType saved successfully.','status'=>true));
        }
        
    }
    public function deleteSurveyType(){
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->SurveyType->useDbConfig = $this->Session->read('ds');
        $data = array();
        $data['type_pkey']          = $_GET['ids'];
        $data['modified_by']        = $loginUser;
        $data['modification_date']  = date('Y-m-d H:i:s');
        $data['status']             = 0;
        $this->SurveyType->save($data,true,array('modified_by','modification_date','status'));
        echo json_encode(array('msg' => 'SurveyType deleted successfully.'));
    }
    /*** Survey Category Master ***/
    public function surveyCategory(){
        $survey_types = $this->getSurveyTypes(true);
        $this->autoRender = true;
        $this->set('survey_types',$survey_types);
    }
    public function getSurveyCategory($asArray = false,$fields ='category_pkey,type_fkey as survey_type, category_code,category_name,category_order'){
        
        $survey_type_cond = (isset($_POST['survey_type']) && !empty($_POST['survey_type']))? " and type_fkey = ".$_POST['survey_type']."":"";
        $this->layout = '';
        $this->autoRender = false;
        $this->SurveyCategory->useDbConfig = $this->Session->read('ds');
        $result = $this->SurveyCategory->query('select '.$fields.' from survey_category where status = 1 '.$survey_type_cond.'');

        if ($asArray == true)
            return $result;
        $survey_categories = array();
        foreach ($result as $key => $val) {
             $survey_categories[] =  $val['survey_category'];
        }
        echo json_encode($survey_categories);
    }
    public function surveyCategoryForm($category_id = ""){
        $survey_types = $this->getSurveyTypes(true);
        $this->autoRender = true;
        $this->set('survey_types',$survey_types);
        
        if($category_id != ''){
            $this->SurveyCategory->useDbConfig = $this->Session->read('ds');
            $result = $this->SurveyCategory->query('select category_pkey,type_fkey as survey_type, category_code,category_name,category_order from survey_category where  category_pkey='.$category_id.' and status = 1 ');  

            if (isset($result[0]) && isset($result[0]['survey_category'])){
                $survey_type = (isset($result[0]['survey_category']['survey_type']) ? $result[0]['survey_category']['survey_type'] : "" );
                $this->set('survey_type',$survey_type);
                $this->set('category_code',(isset($result[0]['survey_category']['category_code']) ? $result[0]['survey_category']['category_code'] : "" ));
                $this->set('category_name',(isset($result[0]['survey_category']['category_name']) ? $result[0]['survey_category']['category_name'] : "" ));
            }
        }else{
            $this->set('survey_type',"");
        }
        $this->set('category_pkey',$category_id);  
    }
    public function getCategoryOrder(){
        
        $this->layout = '';
        $this->autoRender = false;
        $survey_type = (isset($_POST['survey_type']) && !empty($_POST['survey_type']))? $_POST['survey_type']:"";
        $category_order = array();
        if ($survey_type != ""){
            $this->SurveyCategory->useDbConfig = $this->Session->read('ds');
            $result = $this->SurveyCategory->query('select category_order  from survey_category where type_fkey = '.$survey_type.' and status =1 order by category_order ASC ');

            foreach ($result as $key => $val) {
                $category_order[] =  intval($val['survey_category']['category_order']);
            }
        }
        echo json_encode($category_order);
    }
    public function swapSurveyCategoryOrder($data){

        $prev_category_order = $data['prev_category_order'];
        $new_category_order  = $data['category_order'];
        $survey_type         = $data['survey_type'];
        
        if ($new_category_order != $prev_category_order){
            $this->SurveyCategory->useDbConfig = $this->Session->read('ds');
            $result = $this->SurveyCategory->query('update survey_category set category_order = '.$prev_category_order.'
             where type_fkey = '.$survey_type.' and category_order= '.$new_category_order.'');
        }
    }
    public function saveSurveyCategory(){

      
        $this->layout = '';
        $this->autoRender = false;
        $data = $_POST;
        $category_code = isset($data['category_code']) && !empty($data['category_code']) ? $data['category_code'] :'';
        $category_name = isset($data['category_name']) && !empty($data['category_name']) ? $data['category_name'] :'';
        $survey_type   = isset($data['survey_type'])   && !empty($data['survey_type'])   ? $data['survey_type']   :'';
        $action        = (isset($data['category_pkey']) && (!empty($data['category_pkey'])))?'edit':'add';
        

        $this->SurveyCategory->useDbConfig = $this->Session->read('ds');

        $duplicate_condition = ($action == 'edit')?" and category_pkey != ".$data['category_pkey']."" :"";
       
        $duplicateData = $this->SurveyCategory->query("select IF(category_code LIKE '".$category_code."','CODE','NONE') AS CODE ,IF(category_name LIKE '".$category_name."','NAME','NONE') AS NAME from survey_category where type_fkey = ".$survey_type." and (category_code LIKE '".$category_code."' or category_name LIKE '".$category_name."') ".$duplicate_condition."" );
        
        if (!empty($duplicateData)){
            $duplicates = array();
            foreach ($duplicateData as $key => $val) {
                $type = ($val[0]['CODE'] == 'CODE')?'CODE':(($val[0]['NAME'] == 'NAME')?'NAME':'');
                if ($type != '')
                    $duplicates[] = $type;
            }   
             echo json_encode(array('data' => array_unique($duplicates),'status'=>false));
        }else{
            $loginUser = $this->Session->read('login_user_id');
            
            $data['type_fkey'] = $data['survey_type'];
            if ($action == 'add'){
                $data['created_by'] = $loginUser;
                $data['creation_date'] = date('Y-m-d H:i:s');
                $this->SurveyCategory->save($data);
            }else{
                $data['modified_by'] = $loginUser;
                $data['modification_date'] = date('Y-m-d H:i:s');
                $this->swapSurveyCategoryOrder($data);
                $this->SurveyCategory->save($data,true,array('category_code','category_name','category_order','modified_by','modification_date'));
            } 
            echo json_encode(array('msg' => 'Survey Category saved successfully.','status'=> true));
        } 
    }
    public function deleteSurveyCategory(){
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->SurveyCategory->useDbConfig = $this->Session->read('ds');
        $data = array();
        $data['category_pkey']      = $_GET['ids'];
        $data['modified_by']        = $loginUser;
        $data['modification_date']  = date('Y-m-d H:i:s');
        $data['status']             = 0;
        $this->SurveyCategory->save($data,true,array('modified_by','modification_date','status'));
        echo json_encode(array('msg' => 'Survey Category deleted successfully.'));
    }
    /*** Options ***/
    public function options(){
        $survey_types = $this->getSurveyTypes(true);
        $this->set('survey_types',$survey_types);

        $fields = ' category_pkey, category_code ';
        $survey_categories = $this->getSurveyCategory(true,$fields);
        $this->set('survey_categories',$survey_categories);
        $this->autoRender = true;

    }
    public function getOptionsList($asArray = false,$fields=''){

        $cond  = (isset($_POST['survey_type']) && !empty($_POST['survey_type']))? " and oi.type_fkey = ".$_POST['survey_type']."":"";
        $cond .= (isset($_POST['survey_category']) && !empty($_POST['survey_category']))? " and oi.category_fkey = ".$_POST['survey_category']."":"";
        $this->layout = '';
        $this->OptionItems->useDbConfig = $this->Session->read('ds');
        if ($fields == '')
            $fields = 'option_items_pkey as o_id,option_items_name as o_name, option_items_order as o_order,category_fkey as category_id,option_item_type as o_type';
        $query = 'select '.$fields. ',GROUP_CONCAT(oiv.option_items_values order by oiv.option_values_order ASC) as o_values 
                        FROM `option_items` as oi 
                        LEFT JOIN option_items_values as oiv  ON oi.option_items_pkey = oiv.option_items_fkey and oiv.status=1
                        WHERE  oi.status= 1  '.$cond.' group by oi.`option_items_pkey`';

        $result = $this->OptionItems->query($query);

        if ($asArray == true)
            return $result;
        $this->autoRender = false;
        $option_items = array();
        foreach ($result as $key => $val) {
             $option_items[] =  array_merge($val['oi'],$val[0]);
        }
        echo json_encode($option_items);

    }
    public function optionsForm($option_id=""){
        $survey_types = $this->getSurveyTypes(true);
        $this->set('survey_types',$survey_types);
        $this->set('option_id',$option_id);
        
        if($option_id != ''){

            $this->OptionItems->useDbConfig = $this->Session->read('ds');
            $result = $this->OptionItems->query('select type_fkey as survey_type_id,category_fkey as category_id,option_items_name as o_name,option_items_order as o_order,option_item_type as o_type from option_items where  option_items_pkey='.$option_id.' and status = 1 ');  
            if (isset($result[0]) && isset($result[0]['option_items'])){
                $survey_type_id = (isset($result[0]['option_items']['survey_type_id']) ? $result[0]['option_items']['survey_type_id'] : "" );
                $this->set('survey_type_id',$survey_type_id);
                $this->set('category_id',(isset($result[0]['option_items']['category_id']) ? $result[0]['option_items']['category_id'] : "" ));
                $this->set('o_name',(isset($result[0]['option_items']['o_name']) ? $result[0]['option_items']['o_name'] : "" ));
                $this->set('o_order',(isset($result[0]['option_items']['o_order']) ? $result[0]['option_items']['o_order'] : "" ));
                $this->set('o_type',(isset($result[0]['option_items']['o_type']) ? $result[0]['option_items']['o_type'] : "" ));
            }
            $this->OptionItemsValues->useDbConfig = $this->Session->read('ds');
            $itemResults = $this->OptionItemsValues->query('select option_items_values_pkey,option_items_values  ,option_values_order  from option_items_values where  option_items_fkey='.$option_id.' and status = 1 order by option_values_order');  
           
             $template = '<div class="col-sm-12" style="padding-bottom: 5px;">
                                <label class="col-sm-4 control-label" ></label>
                                <div class="col-sm-6">
                                     <input class="form-control" placeholder="Please Enter Option Item Value" type="text" value="{:option_item_val}" name="option_items_values[]" id="option_items_values[]" >
                                      <input type="hidden" value="{:option_id}" name="option_items_id[]" id="option_items_id[]" >
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-danger  btn-sm" option_id="{:option_id}" onclick="deleteItem($(this)); "><i class="fa fa-trash"></i></button>
                                </div>
                            </div>';
            $optionItemValueHtml = '';
            foreach ($itemResults as $key => $val) {
                $optionItemValueHtml .= str_replace(array('{:option_item_val}','{:option_id}'),
                    array($val['option_items_values']['option_items_values'],$val['option_items_values']['option_items_values_pkey']),
                    $template);

            }
            $this->set('optionItemValueHtml',$optionItemValueHtml);
        }else{
            $this->set('survey_type_id','');
        }

    }
    public function getSurveyCategoryBySurveyType(){
        $this->layout = '';
        $this->autoRender = false;
        $survey_type = ( (isset($_POST['survey_type'])) && (!empty($_POST['survey_type']))) ? $_POST['survey_type'] : '';
        if($survey_type != ''){
            $fields = ' category_pkey, category_code ';
            $survey_categories = $this->getSurveyCategory(false,$fields);    
        }else{
            echo json_encode(array());
        }
    }
    public function saveOption(){
      
        $this->layout     = '';
        $this->autoRender = false;

        $data             = $_POST;

        $survey_type      = isset($data['survey_type'])     && !empty($data['survey_type'])     ? $data['survey_type']     :'';
        $survey_category  = isset($data['survey_category']) && !empty($data['survey_category']) ? $data['survey_category'] :'';
        $option_items_name    = isset($data['option_items_name'])   && !empty($data['option_items_name'])   ? $data['option_items_name']   :'';
        $option_items_order   = isset($data['option_items_order'])  && !empty($data['option_items_order'])  ? $data['option_items_order']  :'';
        $action               = (isset($data['option_id']) && (!empty($data['option_id'])))?'edit':'add'; 
  
        $this->OptionItems->useDbConfig = $this->Session->read('ds');
        $this->OptionItemsValues->useDbConfig = $this->Session->read('ds');

        $duplicate_condition = ($action == 'edit')?" and option_items_pkey != ".$data['option_id']."" :"";

        $duplicateData = $this->OptionItems->query("select option_items_pkey from option_items where type_fkey = ".$survey_type." and category_fkey =".$survey_category." and status = 1 and option_items_name LIKE '".$option_items_name."'".$duplicate_condition."" );
        
        if (!empty($duplicateData)){
             echo json_encode(array('msg'=>'Option item name already exists.','status'=>false));
        }else{
            $loginUser = $this->Session->read('login_user_id');
            
            $data['type_fkey']     = $survey_type;
            $data['category_fkey'] = $survey_category;

            if ($action == 'add'){
                $data['created_by']    = $loginUser;
                $data['creation_date'] = date('Y-m-d H:i:s');
                $this->OptionItems->save($data);
                 $data['option_items_fkey']= $this->OptionItems->getLastInsertId();
                 if (isset($data['option_items_values'])){
                    foreach ($data['option_items_values'] as $key => $value) {
                        $data['option_items_values'] = $value;
                        $data['option_values_order'] = $key+1;
                        $this->OptionItemsValues->clear();
                        $this->OptionItemsValues->save($data);
                    }
                 }
            }else{
                $data['option_items_pkey'] = $data['option_id'];
                $data['modified_by']       = $loginUser;
                $data['modification_date'] = date('Y-m-d H:i:s');
                $this->swapOptionItemOrder($data);
                $this->OptionItems->save($data,true,array('option_items_name','option_item_type','option_items_order','modified_by','modification_date'));
                if(isset($data['option_items_values'])){
                    foreach ($data['option_items_values'] as $key => $value) {
                        $data['option_items_values'] = $value;
                        $data['option_values_order'] = $key+1;
                        if (isset($data['option_items_id']) && isset($data['option_items_id'][$key])){
                            $data['option_items_values_pkey'] = $data['option_items_id'][$key];  
                            $this->OptionItemsValues->clear();
                            $this->OptionItemsValues->save($data,true,array('option_items_values','option_values_order','modified_by','modification_date'));
                            unset($data['option_items_values_pkey']);    
                        }else{
                            $data['option_items_fkey'] = $data['option_id'];
                            $data['created_by']       = $loginUser;
                            $this->OptionItemsValues->clear();
                           $this->OptionItemsValues->save($data);  
                        }
                        
                    }
                }
            $this->deleteOptionItemValues($data);
                
            } 
            echo json_encode(array('msg' => 'Survey Category saved successfully.','status'=> true));
        } 
    }
    public function deleteOptionItemValues($data){
        if(isset($data['deletedOptionValues']) && $data['deletedOptionValues'] != ""){
            $this->OptionItemsValues->useDbConfig = $this->Session->read('ds');
            $deleteQuery = 'update option_items_values set status = 0 where option_items_values_pkey in ('.$data['deletedOptionValues'].')'; 

             $this->OptionItemsValues->query($deleteQuery);
        }

    }
    public function getOptionsOrder(){
        
        $this->layout     = '';
        $this->autoRender = false;
        $survey_type      = (isset($_POST['survey_type'])     && !empty($_POST['survey_type']))    ? $_POST['survey_type']:"";
        $survey_category  = (isset($_POST['survey_category']) && !empty($_POST['survey_category']))? $_POST['survey_category']:"";
        $option_order     = array();
        if ($survey_type != ""){
            $this->OptionItems->useDbConfig = $this->Session->read('ds');
            $result = $this->OptionItems->query('select option_items_order from option_items where type_fkey = '.$survey_type.' and category_fkey = '.$survey_category.' and status = 1 order by option_items_order ASC ');

            foreach ($result as $key => $val) {
                $option_order[] =  intval($val['option_items']['option_items_order']);
            }
        }
        echo json_encode($option_order);
    }
    public function swapOptionItemOrder($data){

        $prev_option_order = $data['prev_option_order'];
        $new_option_order  = $data['option_items_order'];
        $survey_type       = $data['survey_type'];
        $survey_category   = $data['survey_category'];
        
        if ($new_option_order != $prev_option_order){
            $this->OptionItems->useDbConfig = $this->Session->read('ds');
            $result = $this->OptionItems->query('update option_items set option_items_order = '.$prev_option_order.'
             where type_fkey = '.$survey_type.' and category_fkey = '.$survey_category.' and option_items_order= '.$new_option_order.'');
        }
    }
    public function deleteOptionItem(){
        $this->layout = '';
        $this->autoRender = false;
        $loginUser = $this->Session->read('login_user_id');
        $this->OptionItems->useDbConfig = $this->Session->read('ds');
        $data = array();
        $data['option_items_pkey']  = $_GET['ids'];
        $data['modified_by']        = $loginUser;
        $data['modification_date']  = date('Y-m-d H:i:s');
        $data['status']             = 0;
        $this->OptionItems->save($data,true,array('modified_by','modification_date','status'));
        echo json_encode(array('msg' => 'Survey Option Item deleted successfully.'));
    }
    /****Tickets *****/
    function getSites(){
        $this->EfsrSite->useDbConfig = $this->Session->read('ds');
        $sites = $this->EfsrSite->query('select efsr_site_pkey,site_name from efsr_site JOIN efsr_tickets on (efsr_site_pkey = site_fkey ) where efsr_site.status = 1 group by efsr_site_pkey  order by site_name');
        $this->set('sites',$sites);
    }
    public function tickets(){

        $this->getSites();
        
        $this->EfsrTickets->useDbConfig = $this->Session->read('ds');
        $status = $this->EfsrTickets->query("select distinct efsr_tickets.status from efsr_tickets where efsr_tickets.status!=0");
        $this->set('status',$status);

    }
    public function tickets_filter(){
        $this->getSites();
    }
    public function getEquipments(){

        $this->layout     = '';
        $this->autoRender = false;
        $efsr_cond = $emp_cond = '';
        if (isset($_POST['site_id']) && !empty($_POST['site_id'])){
            $efsr_cond = " and efsr_equipments_master.site_fkey = ".$_POST['site_id'];
            $emp_cond  = " and efsr_tickets.site_fkey  = ".$_POST['site_id'];
        }

        $this->EfsrEquipmentsMaster->useDbConfig = $this->Session->read('ds');
        $equipments_result = $this->EfsrEquipmentsMaster->query('select efsr_equipments_master_pkey as equipment_pkey ,equipments_name,manufacturer  from efsr_equipments_master JOIN efsr_tickets on (efsr_equipments_master_pkey = efsr_equipments_master_fkey  '.$efsr_cond.' ) where efsr_equipments_master.status = 1 group by efsr_equipments_master_pkey  order by equipments_name,manufacturer');

        $equipments = array();
        foreach ($equipments_result as $key => $val) {
            $equipments[] = $val['efsr_equipments_master'];
        }
        $this->EmpDetails->useDbConfig = $this->Session->read('ds');
        $emp_result = $this->EmpDetails->query('select emp_pkey ,first_name from emp_details JOIN efsr_tickets on (emp_pkey = emp_fkey '.$emp_cond.') where emp_details.status = 1 group by emp_pkey order by first_name');

        $technicians = array();
        foreach ($emp_result as $key => $val) {
            $technicians[] = $val['emp_details'];
        }
        echo json_encode(array('equipments'=>$equipments, 'technicians'=>$technicians));
        
    }

    public function getTickets($status = 0){
        $this->layout     = '';
        $this->autoRender = false;

        $loginUser = $this->Session->read('login_user_id');
        $this->EfsrTickets->useDbConfig = $this->Session->read('ds');

        $site_cond       = (isset($_POST['site_id']) && !empty($_POST['site_id']))? " and efsr_site_pkey = ".$_POST['site_id']."":"";
        $equipment_cond  = (isset($_POST['equipment']) && !empty($_POST['equipment']))? " and efsr_equipments_master_pkey = ".$_POST['equipment']."":"";
        $technican_cond  = (isset($_POST['technician']) && !empty($_POST['technician']))? " and emp_details.emp_pkey = ".$_POST['technician']."":"";
        $status_cond     = (isset($_POST['status']) && !empty($_POST['status']))? " efsr_tickets.status = ".$_POST['status']."":" efsr_tickets.status != '0' ";
        $cond = '';
        if($status == 0 ){
            $cond = $status_cond;
        }else if ($status == 2 ){
            $cond = "efsr_tickets.status = 2 and  emp_approved.emp_pkey in
                    (select emp_fkey from user_credentials where user_id='".strtoupper($loginUser)."')";
        }else if ($status == 3 ){
            $cond = "efsr_tickets.status = 3 and  emp_approved.emp_pkey in
                    (select emp_fkey from user_credentials where user_id='".strtoupper($loginUser)."')";
        }

        $result = $this->EfsrTickets->query("select efsr_tickets_pkey, ticket_no, survey_type_fkey, concat(type_code,' ',type_name)type_name, emp_fkey,
                                    emp_details.first_name, emp_approved.first_name approved_by, concat(equipments_name,'|',manufacturer) 
                                    equipments_name, efsr_id, site_name, sap_site_id, site_id,efsr_tickets.status
                                    FROM efsr_tickets join efsr_site on (efsr_site.efsr_site_pkey = site_fkey ".$site_cond.")
                                    JOIN survey_type on (type_pkey=survey_type_fkey)
                                    JOIN emp_details on (emp_details.emp_pkey=emp_fkey ".$technican_cond." )
                                    JOIN emp_details as emp_approved on (emp_approved.emp_pkey=approved_by)
                                    JOIN efsr_equipments_master on (efsr_equipments_master_fkey=efsr_equipments_master_pkey ".$equipment_cond.")
                                    where ".$cond." order by 1 desc  ");         
                                                      

        $tickets = array();
        foreach ($result as $key => $val) {
            $tickets[] = array_merge($val['efsr_tickets'],$val['emp_details'],$val['emp_approved'],$val[0],$val['efsr_site']);
        }
        echo json_encode($tickets);
    }
    public function preview($ticket_id,$status=0){
        $preview_url  = 'Survey/reports/'.$ticket_id.'/0?t='.time();
        $download_url = 'Survey/reports/'.$ticket_id.'/1?t='.time();
        $this->set('preview_url',$preview_url);
        $this->set('download_url',$download_url);
        $this->set('status',$status);
        $this->set('ticket_id',$ticket_id);
        

    }
    public function getCategories($data){
        $categories = array();
        $report_data = array();
        $image = array();
        foreach ($data as $key => $val) {

            $categories[$val['option_items']['category_fkey']] = $val['survey_category']['category_name'];
            
            if($val['option_items']['option_item_type'] == 'Image'){
                $image[$val['option_items']['category_fkey']][] = array_merge($val['efsr_tickets_cat_trans'],$val['option_items']);
            }else{
                $report_data[ $val['option_items']['category_fkey']][] = array_merge($val['efsr_tickets_cat_trans'],$val['option_items']);
            }
        }

        return array('categories'=>$categories,'report_data'=>$report_data,'image'=>$image);
    }
    public function getLastServiceHtml($efsr_equipments_master_fkey){

        $lastServices = $this->EfsrTickets->query("SELECT ticket_no as service_no,emp_details.first_name as technician,
                emp_approved.first_name approved_by,efsr_tickets.creation_date as service_date
                from efsr_tickets join efsr_site on (efsr_site.efsr_site_pkey = site_fkey)
                join survey_type on (type_pkey=survey_type_fkey)
                join emp_details on (emp_details.emp_pkey=emp_fkey)
                join emp_details as emp_approved on (emp_approved.emp_pkey=approved_by)
                join contacts on (contact_id_fkey=contact_id)
                join efsr_equipments_master on (efsr_equipments_master_fkey=efsr_equipments_master_pkey)
                where efsr_tickets.status = '1' and efsr_equipments_master_fkey= ".$efsr_equipments_master_fkey." order by service_date desc limit 4");

        $html = '<table border="1" cellpadding="1" cellspacing="1" style="width: 100%;">
             <tbody>
                <tr>
                    <td colspan="6" class="gray center">Past Services (Last 4 Services)</td>
                </tr> 
                <tr>
                    <th> SN </th>
                    <th> Service Type </th>
                    <th> Service Date </th>
                    <th> Service Hours </th>
                    <th> MAGIEC Name </th>
                    <th> Technician Name </th>
                </tr>';
        foreach ($lastServices as $key => $val) {
            $html.='<tr>
                        <td>'.$val['efsr_tickets']['service_no'].'</td>
                        <td>&nbsp;</td>
                        <td>'.$val['efsr_tickets']['service_date'].'</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>'.$val['emp_details']['technician'].'</td>
                    </tr>';
        }      
        $html .= '</tbody>
                </table>';
        return $html;
    }
    public function reports($ticket_id,$preview=0){
        
        $this->EfsrTickets->useDbConfig = $this->Session->read('ds');
        $result = $this->EfsrTickets->query("SELECT efsr_tickets_pkey,ticket_no,survey_type_fkey,type_name,emp_fkey,    
                emp_details.first_name,emp_approved.first_name approved_by,
                concat(equipments_name,'|',manufacturer) equipments_name,model_no,
                efsr_id,site_name,sap_site_id,site_id,supplied_by,
                contacts.address customer,(select type_code from  survey_type where type_pkey=equipments_type) 
                application,latitude,longitude,efsr_site.address,equipments_spec spec,equipments_sn,
                efsr_tickets.creation_date ,efsr_equipments_master_fkey
                from efsr_tickets join efsr_site on (efsr_site.efsr_site_pkey = site_fkey)
                join survey_type on (type_pkey=survey_type_fkey)
                join emp_details on (emp_details.emp_pkey=emp_fkey)
                join emp_details as emp_approved on (emp_approved.emp_pkey=approved_by)
                join contacts on (contact_id_fkey=contact_id)
                join efsr_equipments_master on (efsr_equipments_master_fkey=efsr_equipments_master_pkey)
                where ticket_no = ".$ticket_id."");
                //where efsr_tickets.status = '1' and ticket_no = ".$ticket_id."");
        
        $efsr_equipments_master_fkey = (isset($result[0]) && isset($result[0]['efsr_tickets']) && isset($result[0]['efsr_tickets']['efsr_equipments_master_fkey']))?$result[0]['efsr_tickets']['efsr_equipments_master_fkey']:0;
        
        

        $lastServicesHtml = $this->getLastServiceHtml($efsr_equipments_master_fkey);
        $this->set('lastServicesHtml',$lastServicesHtml);
        $data = $this->EfsrTickets->query("select category_name,option_items_pkey,option_items.category_fkey,
            option_items.option_items_name,option_items_values,option_items_order,option_item_type,
            efsr_tickets_cat_trans.category_fkey
            from efsr_tickets_cat_trans 
            left join option_items on (option_items_fkey=option_items_pkey) 
            left join efsr_tickets  on (efsr_tickets_fkey = efsr_tickets_pkey)
            left join survey_category on (efsr_tickets_cat_trans.category_fkey=category_pkey) 
            where ticket_no=".$ticket_id."
            order by 3,6");

        $dynamicData = $this->getCategories($data);
        $categories = $dynamicData['categories'];
        $report_data = $dynamicData['report_data'];
        $image = $dynamicData['image'];
        $reportHtml = '';

        foreach ($categories as $key => $val) {
            $count = isset($report_data[$key])?sizeof($report_data[$key]):0;
            $imageCount = isset($image[$key])?sizeof($image[$key]):0; 
            $moreThanTwoImages = ($imageCount>2) ?true : false;
            $balanceImageCount = ($moreThanTwoImages)?$imageCount:$imageCount - 2 ;
            
            if($count!=0){
                
                $tableCount = ($imageCount == 0 || $moreThanTwoImages)?3:2; 
                
                $rows  = ceil($count / $tableCount ); 
                $tot_count = $rows * $tableCount; 
                $category = isset($report_data[$key])?$report_data[$key]:array();
                $html = '<table border="1" cellpadding="1" cellspacing="1" style="width: 100%;">
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="gray center">'.$val.'</td>
                                    </tr>';
                
                $template = '';
                for($i=0,$col=1;$i<=$tot_count;$i++,$col++){
                    $imageRow = '';
                    if($col > $tableCount){
                        if($tableCount ==2 && $i ==2){
                            
                            if($imageCount==1){                            
                                $imageRow = '<td class="td32 center" rowspan="'.$rows.'"><img src="'.$image[$key][0]['option_items_values'].'" style="height: 100px;max-width: 100%;"/> </td></td>';
                            }elseif ($imageCount ==2 ){
                                $imageRow = '<td  rowspan="'.$rows.'" count="'.$imageCount.'"class="center"> <img src="'.$image[$key][0]['option_items_values'].'" style="height: 100px;max-width: 100%;"/> </td>
                                             <td  rowspan="'.$rows.'"  class="center"><img src="'.$image[$key][1]['option_items_values'].'" style="height: 100px;max-width: 100%;"/>  </td></td>';
                            }
                        }
                        
                        $html .= '<tr>'.$template.$imageRow.'</tr>';
                        $col = 1;
                        $template = '';
                    }

                    $name = isset($category[$i])?$category[$i]['option_items_name']:'&nbsp;';
                    $value = isset($category[$i])?$category[$i]['option_items_values']:'&nbsp;';
                    $template .= '  <th>'.$name.'</th>
                            <td>'.$value.'</td>';
                      
                   
                }
                 $html .= '  </tbody>
                        </table>';
                
                if($balanceImageCount >= 1){
                        $columnsCount = 3;
                        $imageRows  = ceil($balanceImageCount / $columnsCount  ); 
                        
                        $tot_img_count = $imageRows * $columnsCount; 
                        
                        $imageHtmlRow = '';
                        $imagTD = '';

                        for($k=0,$imgColumn=1;$k<$tot_img_count;$k++,$imgColumn++){    
                            $index = $moreThanTwoImages ? $k : $k+2;
                            $imagTD .= isset($image[$key][$index])?'<td class="center"><img src="'.$image[$key][$index]['option_items_values'].'" style="height: 100px;max-width: 100%;"/> </td>':'<td class="center">&nbsp;</td>';
                            if($imgColumn >= $columnsCount){
                                $imageHtmlRow .= '<tr>'.$imagTD.'</tr>';
                                $imagTD = '';
                                $imgColumn = 0;
                            }
                        }
                        $html.= '<table border="1" cellpadding="1" cellspacing="1" style="width: 100%;">
                                    <tbody>
                                    '.$imageHtmlRow.'
                                    </tbody>
                                </table>';  

                } 
                $reportHtml .= $html;
            }else{

                $imageRowHtml = '';
                $imageTitleRowHtml = '';
                $width = 100 / 8;
                $width = $width." %;";
                $imageArr = $image[$key];
                
                for($i=0;$i<$imageCount;$i++){
                    $imageRowHtml .='<td style="'.$width.'" class="center"><img src="'.$imageArr[$i]['option_items_values'].'" style="height: 100px;max-width: 100%;"/> 
                                    </td>';
                    $imageTitleRowHtml .= '<th style="'.$width.'" class="center">'.$imageArr[$i]['option_items_name'].'</th>';
                }

               
               $reportHtml .=  '<table border="1" cellpadding="1" cellspacing="1" style="width: 100%;">
                                    <tbody>
                                        <tr>'.$imageRowHtml.'</tr>
                                        <tr>'.$imageTitleRowHtml.'</tr>
                                    </tbody>
                                </table>';
            }
            
        }
     
        $this->set('result',isset($result[0])?$result[0]:array());
        $this->set('reportHtml',$reportHtml);

        $dompdf = new Dompdf();
        $html = $this->render('reports');
        if($preview==10){
            echo $html;exit;
        }
        $dompdf->loadHtml($html);
        $dompdf->set_option('isRemoteEnabled', true);
        
        /* Render the HTML as PDF */
        $dompdf->render();
        /* Output the generated PDF to Browser */
        if($preview==0){
            $dompdf->stream("file.pdf", array("Attachment" => false));    
        }else{
          $dompdf->stream();  
        }

    }
    function ticketApprove(){

        $this->layout = '';
        $this->autoRender = false;
        $ticket_id = (isset($_POST['ticket_id']) && !empty($_POST['ticket_id']))? $_POST['ticket_id']:'';
        if($ticket_id==''){
            echo json_encode(array('msg'=>'Ticket id is missing. Please try again','status'=>false));
            exit;
        }
        $this->EfsrTickets->useDbConfig = $this->Session->read('ds');
        $result = $this->EfsrTickets->query("update efsr_tickets SET approved_date = '".date('Y-m-d H:i:s')."',
                status = 3
                WHERE ticket_no  = ".$ticket_id.""); 
        echo json_encode(array('msg'=>'Ticket approved successfully','status'=>true));
    }
}
