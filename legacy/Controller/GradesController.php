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
class GradesController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Grades';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Grades','Holiday','EmployeeProfessionalDetails');
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
        $arr_count=$this->Grades->find("count",array('conditions'=>array('status'=>1),'order'=>'grade_pkey DESC'));
        foreach ($arr_banks as $key => $value) {
            // debug($value);
            $resp_banks["grade_pkey"]=$value["Grades"]["grade_pkey"];
            $resp_banks["grade_code"]=$value["Grades"]["grade_code"];
            $resp_banks["grade_name"]=$value["Grades"]["grade_name"];
            $resp_banks["status"]=$value["Grades"]["status"];
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
            $this->set("grades", $grades);
            // debug($grades);
            $data['grade_pkey']=$grades[0]['grade']['grade_pkey'];
            $data['grade_code']=$grades[0]['grade']['grade_code'];
            $data['grade_name']=$grades[0]['grade']['grade_name'];
            $data['status']=$grades[0]['grade']['status'];            
        }else{
            $data['grade_pkey']='';
            $data['grade_code']='';
            $data['grade_name']='';
            $data['status']='';
        }
        $this->set("data", $data);
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
        $grade_code = $arr_form_data['grade_code'];
        $grade_name = $arr_form_data['grade_name'];

        $result1=$this->Grades->query("select count(*) as count from grade where grade_code='$grade_code' and status=1");
        $result2=$this->Grades->query("select count(*) as count from grade where grade_name='$grade_name' and status=1");


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
                // $result =   $this->Grades->update($data,array('grade_pkey'=>$data['grade_pkey']));
                $grade_pkey="grade_pkey=".$data['grade_pkey']."";
                $grade_code="grade_code='".$data['grade_code']."'";
                $grade_name="grade_name='".$data['grade_name']."'";
                $this->Grades->query("update grade set $grade_pkey , $grade_code , $grade_name where $grade_pkey and status=1");
                $resp["msg"]="Updated Successfully";
            }else{
                $result =   $this->Grades->save($data);
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
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $resp=array();
        // $resp = array('success' => 0 );
       
        $asiigned = $this->EmployeeProfessionalDetails->find("count",array("conditions"=>array('EmployeeProfessionalDetails.emp_grade' => $grade_pkey)));
      //  debug($asiigned);
        $result =   array('success' => 0 );
        if($asiigned > 0 )

         {
                   //edited by sinsiya on 13-03-2024
                   $resp["danger"] = true;
                   $resp["msg"] = "Employees allocated under the selected Grade";
                    // $result['color'] = 'red';
                    
          }
        else{
            if($grade_pkey!=null){
                  $result=$this->Grades->query("update grade set status=0 where grade_pkey=$grade_pkey");
            // if(count($result)){
            //     $resp["msg"]="Deleted Successfully";
            //     $resp["success"]=1;
            // }
                   $resp["success"]=1;
                   $resp["msg"]="Deleted Successfully";
          }
      }
        // if(isset($_REQUEST["ids"]))
        // {
        //     $ar_ids = explode(",", $_REQUEST["ids"]);
        // //  debug($ar_ids);
        //     $this->Grades->updateAll(
        //         array('Grades.status' => 0),
        //         array('Grades.id' => $ar_ids)
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
