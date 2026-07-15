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
    public $uses = array('SurveyType','SurveyCategory','OptionItems','OptionItemsValues');
    public $components = array('MasterdataManagement');

    /*** Survey Type Master ***/
    public function index() {

    }

    public function form($type_pkey=''){
        if($type_pkey != ''){
            $this->SurveyType->useDbConfig = $this->Session->read('ds');
            $result = $this->SurveyType->query('select type_pkey,type_code ,type_name from survey_type where  type_pkey='.$type_pkey.' and status = 1 ');  
        
            if (isset($result[0]) && isset($result[0]['survey_type'])){
                $this->set('type_code',(isset($result[0]['survey_type']['type_code']) ? $result[0]['survey_type']['type_code'] : "" ));
                $this->set('type_name',(isset($result[0]['survey_type']['type_name']) ? $result[0]['survey_type']['type_name'] : "" ));
            }
        }
        $this->set('type_pkey',$type_pkey);
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
        if ($action == 'add'){
            $data['created_by'] = $loginUser;
            $data['creation_date'] = date('Y-m-d H:i:s');
            $this->SurveyType->save($data);
        }else{
            $data['modified_by'] = $loginUser;
            $data['modification_date'] = date('Y-m-d H:i:s');
            $this->SurveyType->save($data,true,array('type_code','type_name','modified_by','modification_date'));
        } 
        echo json_encode(array('msg' => 'SurveyType saved successfully.'));
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
}
