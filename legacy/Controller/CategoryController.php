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
class CategoryController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Category';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Category','Holiday');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */

    ///////////////////////////////GRADE IS CREATED BY ****ARUL P DAS ON 26/11/2019******/////////////
	public function index(){
		$this->layout = FALSE;
		$this->Category->useDbConfig = $this->Session->read('ds');
	}

    public function listGrades(){////This is to view all the categorys in view page. BY **ARUL P DAS on 27/11/2019**
        $this->autoRender=FALSE;
        $this->datatable["conditions"] = array("status"=> 1);
        $this->Category->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        
        $resp_banks = array();
        $data=array();
        $arr_banks = $this->Category->find("all",array('conditions'=>array('status'=>1),'order'=>'category_pkey DESC','limit' => $limit,
    'offset' => $ofst));
        $arr_count=$this->Category->find("count",array('conditions'=>array('status'=>1),'order'=>'category_pkey DESC'));
        foreach ($arr_banks as $key => $value) {
            // debug($value);
            $resp_banks["category_pkey"]=$value["Category"]["category_pkey"];
            $resp_banks["category_code"]=$value["Category"]["category_code"];
            $resp_banks["category_name"]=$value["Category"]["category_name"];
            $resp_banks["status"]=$value["Category"]["status"];
            $data["rows"][$key]=$resp_banks;
        }
        $data["total"]=$arr_count;
        echo json_encode($data);
    }

    public function form($category_pkey=0){
        $this->layout = null;
        $this->Category->useDbConfig = $this->Session->read('ds');
        $data=array();
        if($category_pkey!='' || $category_pkey!=0){//This is to view details in form when edit a category. By ARUL P DAS
            $conditions="and category_pkey=$category_pkey";
            $categorys = $this->Category->query("select * from category where status=1 $conditions");
            $this->set("categorys", $categorys);
            // debug($categorys);
            $data['category_pkey']=$categorys[0]['category']['category_pkey'];
            $data['category_code']=$categorys[0]['category']['category_code'];
            $data['category_name']=$categorys[0]['category']['category_name'];
            $data['status']=$categorys[0]['category']['status'];            
        }else{
            $data['category_pkey']='';
            $data['category_code']='';
            $data['category_name']='';
            $data['status']='';
        }
        $this->set("data", $data);
    }

    public function checkcategoryexists($category_pkey=0){
        ///This is to check whether category name is already exist on category table BY ***ARUL P DAS on 27/11/2019********
        $this->autoRender=FALSE;
        $this->Category->useDbConfig = $this->Session->read('ds');
        $arr_form_data  =   $this->request->data;
        // $data['category_code'] = $arr_form_data['category_code'];
        $category_name = $arr_form_data['category_name'];
        // debug($category_name);
        $result=$this->Category->query("select count(*) as count from category where category_name='$category_name' and status=1");
        // debug($result);
        if($result[0][0]['count']!="0" || $result[0][0]['count']!=0){$resp=1;}else{$resp=0;}
        echo json_encode($resp);
    }

    public function checkcategorycodeexists(){
        ///This is to check whether category code is already exist on category table BY ***ARUL P DAS on 27/11/2019********
        $this->autoRender=FALSE;
        $this->Category->useDbConfig = $this->Session->read('ds');
        $arr_form_data  =   $this->request->data;
        // $data['category_code'] = $arr_form_data['category_code'];
        $category_code = $arr_form_data['category_code'];
        // debug($category_name);
        $result=$this->Category->query("select count(*) as count from category where category_code='$category_code' and status=1");
        // debug($result);
        if($result[0][0]['count']!="0" || $result[0][0]['count']!=0){$resp=1;}else{$resp=0;}
        echo json_encode($resp);
    }
    public function saveGrade(){
        //This is to insert or update category table. Inserts when create a new category and Updates when edit a category. BY ***ARUL P DAS ON 27/11/2019********
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->Category->useDbConfig = $this->Session->read('ds');
        $arr_form_data  =   $this->request->data;
        // debug($arr_form_data);
        $data = array();
        $resp = array();
        $check=0;
        $data['category_pkey'] = $arr_form_data['id'];
        $data['category_code'] = $arr_form_data['category_code'];
        $data['category_name'] = $arr_form_data['category_name'];
        $category_code = $arr_form_data['category_code'];
        $category_name = $arr_form_data['category_name'];

        $result1=$this->Category->query("select count(*) as count from category where category_code='$category_code' and status=1");
        $result2=$this->Category->query("select count(*) as count from category where category_name='$category_name' and status=1");


        if($arr_form_data['id']=='' || $arr_form_data['id']==NULL){
            if($result1[0][0]['count']!="0" || $result1[0][0]['count']!=0){
                $resp["msg"]="Code already exist";
                $resp["success"] = false;
                $check=1;
                echo json_encode($resp);
            }
        }

        if($result2[0][0]['count']!="0" || $result2[0][0]['count']!=0){
            if($arr_form_data['id']!='' || $arr_form_data['id']!=NULL){
                $resp["msg"]="Name already exist";
            }
            $resp["success"] = false;
            $check=1;
            echo json_encode($resp);
        }
        

        if($check!=1){
            //$data['status']    = $arr_form_data['status'];
            if($arr_form_data['id']!='' || $arr_form_data['id']!=NULL){
                // $result =   $this->Category->update($data,array('category_pkey'=>$data['category_pkey']));
                $category_pkey="category_pkey=".$data['category_pkey']."";
                $category_code="category_code='".$data['category_code']."'";
                $category_name="category_name='".$data['category_name']."'";
                $this->Category->query("update category set $category_pkey , $category_code , $category_name where $category_pkey and status=1");
                $resp["msg"]="Updated Successfully";
            }else{
                $result =   $this->Category->save($data);
                $resp["msg"]="Inserted Successfully";
            }
            //debug($result);
            $resp["success"] = true;
            echo json_encode($resp);
        }
    }

    public function deleteGrade($category_pkey=0){
        //This is to delete selected category from category table. BY ***ARUL P DAS ON 27/11/2019********
        $this->autoRender=FALSE;
        $this->Category->useDbConfig = $this->Session->read('ds');
        $resp=array();

        $category_allocation = $this->Category->query("SELECT COUNT(grade_pkey) FROM grade WHERE category_fkey = $category_pkey AND status = 1");

        // $resp = array('success' => 0 );
        $count = isset($category_allocation[0][0]['COUNT(grade_pkey)'])? $category_allocation[0][0]['COUNT(grade_pkey)']:0;
        if($count == 0){
            $resp["success"]=1;
            $resp["msg"]="Deleted Successfully";
    
            if($category_pkey!=null){
                $result=$this->Category->query("update category set status=0 where category_pkey=$category_pkey");
                // if(count($result)){
                //     $resp["msg"]="Deleted Successfully";
                //     $resp["success"]=1;
                // }
            }
        }else{
            $resp["success"]=0;
            $resp["msg"]="Cannot delete an allocated category";
        }


        // if(isset($_REQUEST["ids"]))
        // {
        //     $ar_ids = explode(",", $_REQUEST["ids"]);
        // //  debug($ar_ids);
        //     $this->Category->updateAll(
        //         array('Category.status' => 0),
        //         array('Category.id' => $ar_ids)
        //     );
        //     $result['success'] = 1;
        // }
            
        echo json_encode($resp);
    }

 //    public function newGrade(){
		
	// 	$data['id'] = 0;
	// 	$data['dept_code'] = "";
	// 	$data['dept_name'] = "";
	// 	$data['status'] = "";
		
				
	// 	$this->Category->useDbConfig = $this->Session->read('ds');
	// 	if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
 //            $data_db = $this->Category->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
	// 		// debug($data);
	// 		$data = $data_db['Category'];
	// 	}

	// 	$this->set(compact("data"));
	// 	$this->layout = null;
		
	// }
	
}
