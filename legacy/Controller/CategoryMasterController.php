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
class CategoryMasterController extends AppController {
    
    public $uses = array('Workstatus','CategoryMaster');
    
    /*b
     * Lists workstatus Here
     */
    public function index(){
        
    }
    public function category($category_pkey=0){
    $this->CategoryMaster->useDbConfig = $this->Session->read('ds');
         if($category_pkey == 0){
            $title = 'Add Category';
        }else{
            $title = 'Edit Category';
        }
        $this->set('title', $title);
        $this->layout = NULL;
        $result = $this->CategoryMaster->query("select * from category_master where category_pkey = '$category_pkey'");
   //  debug($result);
     $this->set('result',$result); 
        
    }
    public function categoryfilter(){
        $this->autoRender = false;
        $this->CategoryMaster->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
//        debug($q);
        if ($q != null) {
            $q_condition = "and code like '%$q%'";
        } else {
            $q_condition = "";
        }
        $arr_site = $this->CategoryMaster->query("select * from category_master where status= '1' $q_condition ");
//        debug("select * from category_master where status= '1' $q_condition ");
        $array = array();
        $category[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_site as $key => $value) {
//            debug($value);
            $category[] = array(
                'id' => $value['category_master']['category_pkey'],
                'text' => $value['category_master']['code'] 
            );
        }
        $array['items'] = $category;
//        debug($array['items']);
        echo json_encode($array);
    }
     public function delete($user_pkey = 0)
    {
		 $this->CategoryMaster->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE ; 
       // debug($user_pkey);
        if ($user_pkey != 0) {
            $this->CategoryMaster->updateAll(array('status' => 0), array('category_pkey' => $user_pkey));
            echo json_encode(array('msg' => 'master deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'master deletion failed!'));
        }
        
        }
        
           public function save()
            {
         $this->CategoryMaster->useDbConfig = $this->Session->read('ds');
          $this->autoRender = FALSE;
                    $this->layout = null;
                   
                    $arr_form_data = $this->request->data;
                  //  debug($arr_form_data);
                    $arr_form_data['created_by']   =   $this->Session->read('user_name');
                    $user = $this->Session->read('user_name');
                //edited by megha on 8/01/2020 removed category cannot add again, status condition added.
         $cat_pkey = isset($arr_form_data['category_pkey'])?$arr_form_data['category_pkey']:0;    
                     if ($arr_form_data['category_pkey'] != '') {
            $arr_form_data['modified_by'] = $this->Session->read('user_name');
            $arr_form_data['modified_date'] = date('Y-m-d');
        }
        
        $code = $arr_form_data['code'];
        //edited by megha on 8/01/2020 removed category cannot add again, status condition added.
        $catgory_check = $this->CategoryMaster->find("all", array("conditions"=>array("code" => $code,'status' => 1,'category_pkey !=' => $cat_pkey)));
        
        if(!empty($catgory_check)){
            $msg = "Category Already Exists ";
            $success = false;
        }else {
            $result = $this->CategoryMaster->save($arr_form_data);
            $success = true;
             if($cat_pkey == '0'){
            $msg = "Category Added successfully";
            }else{
             $msg = "Category Updated successfully";    
            }
        }
       // debug($arr_form_data);
        
                    $resp = array();
                    $resp["success"] = $success;
                    $resp["msg"] = $msg;
                    echo json_encode($resp);
        
        
    }
    public function listmaster($param = "")
    {	
        $this->autoRender = false ;
        $this->CategoryMaster->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $arr_data = $this->request->data;
//        
//        if(isset($arr_data['site'])){
//            $site_pke = $arr_data['site'];
//            $cond = "category_pkey = $site_pke ";
//        }else{
//            $cond = "";
//        }
        $categorypkey = isset($arr_data['site']) ? $arr_data['site']: '0';
        if($categorypkey == 0 ){
            $cond = "";
        }
        else {
          $cond = "category_pkey = $categorypkey ";
        }
                     
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'category_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';

        $ofst = ($page - 1) * $limit;
        
        $result_count = $this->CategoryMaster->find("count",array(
             'conditions'=>array('CategoryMaster.status' => 1)
         ));
        
         $result = $this->CategoryMaster->find("all",array(
             'conditions'=>array('CategoryMaster.status' => 1 ,$cond),
                    'order'=>array($sort=>$order),
                    'limit'=>intval($limit),
                    'offset'=>intval($ofst)
         ));
         //debug($result);
         
        $rows = array();
        foreach ($result as $key => $val) {
            $rows[] = $val["CategoryMaster"];
        }

        $resp_data["rows"] = $rows;
        $resp_data["total"] = $result_count;



        echo json_encode($resp_data);
    }
}