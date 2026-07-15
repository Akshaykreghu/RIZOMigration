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
class GradesNewController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'GradesNew';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Grades','Holiday');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */

    ///////////////////////////////GRADE IS CREATED BY ****ARUL P DAS ON 26/11/2019******/////////////
	public function index(){
		$this->layout = FALSE;
		$this->Grades->useDbConfig = $this->Session->read('ds');
	}

    public function listGrades(){////This is to view all the grades in view page. BY **ARUL P DAS on 27/11/2019**
        $this->autoRender=FALSE;
        $this->datatable["conditions"] = array("status"=> 1);
        $this->Grades->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        
        $resp_banks = array();
        $data=array();
        $arr_banks = $this->Grades->find("all",array('conditions'=>array('status'=>1),'order'=>'grade_pkey DESC','limit' => $limit,
    'offset' => $ofst));
        // debug($arr_banks);
        $arr_count=$this->Grades->find("count",array('conditions'=>array('status'=>1),'order'=>'grade_pkey DESC'));
        foreach ($arr_banks as $key => $value) {
            // debug($value);
            $resp_banks["grade_pkey"]=$value["Grades"]["grade_pkey"];
            $resp_banks["grade_code"]=$value["Grades"]["grade_code"];
            $resp_banks["grade_name"]=$value["Grades"]["grade_name"];
            $resp_banks["status"]=$value["Grades"]["status"];
            //Edited by Akshay on 9-10-2023
            $resp_banks["pay_scale"] = $value["Grades"]["pay_scale"];
            $category_fkey = $value["Grades"]["category_fkey"];
            // debug($category_fkey);
            $category = $this->Grades->query("SELECT category_name FROM category WHERE status = 1 AND category_pkey = $category_fkey");
            $resp_banks["category_name"] = isset($category[0]['category']['category_name'])? $category[0]['category']['category_name']:'';
            $data["rows"][$key]=$resp_banks;
        }
        $data["total"]=$arr_count;
        echo json_encode($data);
    }

    public function form($grade_pkey=0){
        $this->layout = null;
        $this->Grades->useDbConfig = $this->Session->read('ds');
        $data=array();
        if($grade_pkey!='' || $grade_pkey!=0){//This is to view details in form when edit a grade. By ARUL P DAS
            $conditions="and grade_pkey=$grade_pkey";
            $grades = $this->Grades->query("select * from grade where status=1 $conditions");
            $category_pkey = isset($grades[0]['grade']['category_fkey'])? $grades[0]['grade']['category_fkey']:'';
            $category = $this->Grades->query("SELECT category_code, category_name FROM category WHERE category_pkey = $category_pkey AND status = '1'");
            $this->set("grades", $grades);

            $data['grade_pkey'] = isset($grades[0]['grade']['grade_pkey']) ? $grades[0]['grade']['grade_pkey'] : '';
            $data['grade_code'] = isset($grades[0]['grade']['grade_code']) ? $grades[0]['grade']['grade_code'] : '';
            $data['grade_name'] = isset($grades[0]['grade']['grade_name']) ? $grades[0]['grade']['grade_name'] : '';
            $data['status'] = isset($grades[0]['grade']['status']) ? $grades[0]['grade']['status'] : '';
            $data['pay_scale'] = isset($grades[0]['grade']['pay_scale']) ? $grades[0]['grade']['pay_scale'] : '';
            $data['category_pkey'] = $category_pkey;
            $data['category_name'] = isset($category[0]['category']['category_name']) ? $category[0]['category']['category_name'] : '';
                      
        }else{
            $data['grade_pkey']='';
            $data['grade_code']='';
            $data['grade_name']='';
            $data['status']='';
            $data['pay_scale']='';
            $data['category_pkey'] = '';
            $data['category_name'] = '';
        }
        $this->set("data", $data);

        $categories = $this->Grades->query("SELECT category_pkey, category_name FROM category WHERE status = 1");
        // debug($categories); exit;
        $this->set("categories", $categories);


    }

    public function checkgradeexists($grade_pkey=0){
        ///This is to check whether grade name is already exist on grade table BY ***ARUL P DAS on 27/11/2019********
        $this->autoRender=FALSE;
        $this->Grades->useDbConfig = $this->Session->read('ds');
        $arr_form_data  =   $this->request->data;
        // $data['grade_code'] = $arr_form_data['grade_code'];
        $grade_name = $arr_form_data['grade_name'];
        // debug($grade_name);
        $result=$this->Grades->query("select count(*) as count from grade where grade_name='$grade_name' and status=1");
        // debug($result);
        if($result[0][0]['count']!="0" || $result[0][0]['count']!=0){$resp=1;}else{$resp=0;}
        echo json_encode($resp);
    }

    public function checkgradecodeexists(){
        ///This is to check whether grade code is already exist on grade table BY ***ARUL P DAS on 27/11/2019********
        $this->autoRender=FALSE;
        $this->Grades->useDbConfig = $this->Session->read('ds');
        $arr_form_data  =   $this->request->data;
        // $data['grade_code'] = $arr_form_data['grade_code'];
        $grade_code = $arr_form_data['grade_code'];
        // debug($grade_name);
        $result=$this->Grades->query("select count(*) as count from grade where grade_code='$grade_code' and status=1");
        // debug($result);
        if($result[0][0]['count']!="0" || $result[0][0]['count']!=0){$resp=1;}else{$resp=0;}
        echo json_encode($resp);
    }
    public function saveGrade(){
        //This is to insert or update grade table. Inserts when create a new grade and Updates when edit a grade. BY ***ARUL P DAS ON 27/11/2019********
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->Grades->useDbConfig = $this->Session->read('ds');
        $arr_form_data  =   $this->request->data;
        // debug($arr_form_data);
        $data = array();
        $resp = array();
        $check=0;
        $data['grade_pkey'] = $arr_form_data['id'];
        $data['grade_code'] = $arr_form_data['grade_code'];
        $data['grade_name'] = $arr_form_data['grade_name'];
        $data['category_fkey'] = $arr_form_data['category_pkey'];
        $data['pay_scale'] = $arr_form_data['pay_scale'];

        $grade_code = $arr_form_data['grade_code'];
        $grade_name = $arr_form_data['grade_name'];

        $result1=$this->Grades->query("select count(*) as count from grade where grade_code='$grade_code' and status=1");
        $result2=$this->Grades->query("select count(*) as count from grade where grade_name='$grade_name' and status=1");

        // debug($result1);
        // debug($result2); exit;

        if($arr_form_data['id']=='' || $arr_form_data['id']==NULL){
            if($result1[0][0]['count']!="0" || $result1[0][0]['count']!=0){
                $resp["msg"]="Code already exist";
                $resp["success"] = false;
                $check=1;
                echo json_encode($resp);
            }
        }

        // if($result2[0][0]['count']!="0" || $result2[0][0]['count']!=0){
        //     if($arr_form_data['id']!='' || $arr_form_data['id']!=NULL){
        //         $resp["msg"]="Name already exist";
        //     }
        //     $resp["success"] = false;
        //     $check=1;
        //     echo json_encode($resp);
        // }
        
        if($check!=1){
            //$data['status']    = $arr_form_data['status'];
            if($arr_form_data['id']!='' || $arr_form_data['id']!=NULL){
                // $result =   $this->Grades->update($data,array('grade_pkey'=>$data['grade_pkey']));
                $grade_pkey="grade_pkey=".$data['grade_pkey']."";
                $grade_code="grade_code='".$data['grade_code']."'";
                $grade_name="grade_name='".$data['grade_name']."'";
                $category_fkey = "category_fkey='".$data['category_fkey']."'";
                $pay_scale = "pay_scale='".$data['pay_scale']."'";
                $this->Grades->query("update grade set $grade_pkey , $grade_code , $grade_name, $category_fkey, $pay_scale where $grade_pkey and status=1");
                $this->Grades->query("UPDATE pay_scale SET $pay_scale WHERE status = 1");
                $resp["msg"]="Updated Successfully";
            }else{

                $result =   $this->Grades->save($data);
                
                //Edited by Akshay on 10-10-2023
                $grade_pkey=$result['Grades']['id'];
                $pay_scale = $data['pay_scale'];
                $this->Grades->query("INSERT INTO pay_scale (grade_fkey, pay_scale) VALUES( $grade_pkey, $pay_scale)");
                $resp["msg"]="Inserted Successfully";
            }
            //debug($result);
            $resp["success"] = true;
            echo json_encode($resp);
        }
    }

    public function deleteGrade($grade_pkey=0){
        //This is to delete selected grade from grade table. BY ***ARUL P DAS ON 27/11/2019********
        $this->autoRender=FALSE;
        $this->Grades->useDbConfig = $this->Session->read('ds');
        $resp=array();
        // $resp = array('success' => 0 );
        $grade_allocation = $this->Grades->query("SELECT COUNT(emp_pkey) FROM employee_info WHERE emp_status = 1 AND emp_pkey IN (SELECT emp_fkey FROM emp_proff WHERE emp_grade='$grade_pkey')");
        $grade_count = isset($grade_allocation[0][0]['COUNT(emp_pkey)'])? $grade_allocation[0][0]['COUNT(emp_pkey)']:0;

        if( $grade_count == 0){
            $resp["success"]=1;
            $resp["msg"]="Deleted Successfully";
    
            if($grade_pkey!=null){
                $result=$this->Grades->query("update grade set status=0 where grade_pkey=$grade_pkey");
                $this->Grades->query("UPDATE pay_scale SET status = 0 WHERE grade_fkey = $grade_pkey");
            }
        }else{
            $resp["success"]=0;
            $resp["msg"]="Cannot delete an allocated grade";
        }


        echo json_encode($resp);
    }

 //    public function newGrade(){
		
	// 	$data['id'] = 0;
	// 	$data['dept_code'] = "";
	// 	$data['dept_name'] = "";
	// 	$data['status'] = "";
		
				
	// 	$this->Grades->useDbConfig = $this->Session->read('ds');
	// 	if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
 //            $data_db = $this->Grades->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
	// 		// debug($data);
	// 		$data = $data_db['Grades'];
	// 	}

	// 	$this->set(compact("data"));
	// 	$this->layout = null;
		
	// }
	
}
