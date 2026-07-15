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
class NoticePeriodController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'NoticePeriod';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'NoticePeriod', 'Holiday');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    ///////////////////////////////NOTICE PERIOD IS CREATED BY ****ARUL P DAS ON 17/11/2020******/////////////
    public function index() {
        $this->layout = FALSE;
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
    }

    public function listNotice() {////This is to view all the notices in view page. BY **ARUL P DAS on 17/11/2020**
        $this->autoRender = FALSE;
        $this->datatable["conditions"] = array("status" => 1);
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;

        $resp_banks = array();
        $data = array();
        $arr_banks = $this->NoticePeriod->find("all", array('conditions' => array('status' => 1), 'order' => 'notice_pkey DESC', 'limit' => $limit,
            'offset' => $ofst));
        $arr_count = $this->NoticePeriod->find("count", array('conditions' => array('status' => 1), 'order' => 'notice_pkey DESC'));
        foreach ($arr_banks as $key => $value) {
            // debug($value);
            $resp_banks["notice_pkey"] = $value["NoticePeriod"]["notice_pkey"];
            $resp_banks["notice_days"] = $value["NoticePeriod"]["notice_days"];
            $resp_banks["description"] = $value["NoticePeriod"]["description"];
            $resp_banks["status"] = $value["NoticePeriod"]["status"];
            $data["rows"][$key] = $resp_banks;
        }
        $data["total"] = $arr_count;
        echo json_encode($data);
    }

    public function form($notice_pkey = 0) {
        $this->layout = null;
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $data = array();
        if ($notice_pkey != '' || $notice_pkey != 0) {//This is to view details in form when edit a notice_period. By ARUL P DAS
            $conditions = "and notice_pkey=$notice_pkey";
            $notice = $this->NoticePeriod->query("select * from notice_period where status=1 $conditions");
            $this->set("notice", $notice);
            // debug($notice);
            $data['notice_pkey'] = $notice[0]['notice_period']['notice_pkey'];
            $data['notice_days'] = $notice[0]['notice_period']['notice_days'];
            $data['description'] = $notice[0]['notice_period']['description'];
            $data['status'] = $notice[0]['notice_period']['status'];
        } else {
            $data['notice_pkey'] = '';
            $data['notice_days'] = '';
            $data['description'] = '';
            $data['status'] = '';
        }
        $this->set("data", $data);
    }

    public function checknotice_periodexists($notice_pkey = 0) {
        ///This is to check whether notice_period name is already exist on notice_period table BY ***ARUL P DAS on 27/11/2019********
        $this->autoRender = FALSE;
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // $data['notice_days'] = $arr_form_data['notice_days'];
        $description = $arr_form_data['description'];
        // debug($description);
        $result = $this->NoticePeriod->query("select count(*) as count from notice_period where description='$description' and status=1");
        // debug($result);
        if ($result[0][0]['count'] != "0" || $result[0][0]['count'] != 0) {
            $resp = 1;
        } else {
            $resp = 0;
        }
        echo json_encode($resp);
    }

    public function checknotice_periodcodeexists() {
        ///This is to check whether notice_period code is already exist on notice_period table BY ***ARUL P DAS on 27/11/2019********
        $this->autoRender = FALSE;
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // $data['notice_days'] = $arr_form_data['notice_days'];
        $notice_days = $arr_form_data['notice_days'];
        // debug($description);
        $result = $this->NoticePeriod->query("select count(*) as count from notice_period where notice_days='$notice_days' and status=1");
        // debug($result);
        if ($result[0][0]['count'] != "0" || $result[0][0]['count'] != 0) {
            $resp = 1;
        } else {
            $resp = 0;
        }
        echo json_encode($resp);
    }

    public function savenotice_period() {
        //This is to insert or update notice_period table. Inserts when create a new notice_period and Updates when edit a notice_period. BY ***ARUL P DAS ON 27/11/2019********
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $data = array();
        $resp = array();
        $check = 0;
        $data['notice_pkey'] = $arr_form_data['id'];
        $data['notice_days'] = $arr_form_data['notice_days'];
        $data['description'] = $arr_form_data['description'];
        $notice_days = $arr_form_data['notice_days'];
        $description = $arr_form_data['description'];

        $result1 = $this->NoticePeriod->query("select count(*) as count from notice_period where notice_days='$notice_days' and status=1");
        $result2 = $this->NoticePeriod->query("select count(*) as count from notice_period where description='$description' and status=1");


        if ($arr_form_data['id'] == '' || $arr_form_data['id'] == NULL) {
            if ($result1[0][0]['count'] != "0" || $result1[0][0]['count'] != 0) {
                $resp["msg"] = "Day already exist";
                $resp["success"] = false;
                $check = 1;
                echo json_encode($resp);
            }
        }

        if ($result2[0][0]['count'] != "0" || $result2[0][0]['count'] != 0) {
            if ($arr_form_data['id'] != '' || $arr_form_data['id'] != NULL) {
                $resp["msg"] = "Description already exist";
            }
            $resp["success"] = false;
            $check = 1;
            echo json_encode($resp);
        }


        if ($check != 1) {
            //$data['status']    = $arr_form_data['status'];
            if ($arr_form_data['id'] != '' || $arr_form_data['id'] != NULL) {
                // $result =   $this->NoticePeriod->update($data,array('notice_pkey'=>$data['notice_pkey']));
                $notice_pkey = "notice_pkey=" . $data['notice_pkey'] . "";
                $notice_days = "notice_days='" . $data['notice_days'] . "'";
                $description = "description='" . $data['description'] . "'";
                $this->NoticePeriod->query("update notice_period set $notice_pkey , $notice_days , $description where $notice_pkey and status=1");
                $resp["msg"] = "Updated Successfully";
            } else {
                $result = $this->NoticePeriod->save($data);
                $resp["msg"] = "Inserted Successfully";
            }
            //debug($result);
            $resp["success"] = true;
            echo json_encode($resp);
        }
    }

    public function deleteNotice($notice_pkey = 0) {
        //This is to delete selected notice_period from notice_period table. BY ***ARUL P DAS ON 27/11/2019********
        $this->autoRender = FALSE;
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $resp = array();
        // $resp = array('success' => 0 );
        $resp["success"] = 1;
        $resp["msg"] = "Deleted Successfully";

        if ($notice_pkey != null) {
            $result = $this->NoticePeriod->query("update notice_period set status=0 where notice_pkey=$notice_pkey");
            // if(count($result)){
            //     $resp["msg"]="Deleted Successfully";
            //     $resp["success"]=1;
            // }
        }

        // if(isset($_REQUEST["ids"]))
        // {
        //     $ar_ids = explode(",", $_REQUEST["ids"]);
        // //  debug($ar_ids);
        //     $this->NoticePeriod->updateAll(
        //         array('NoticePeriod.status' => 0),
        //         array('NoticePeriod.id' => $ar_ids)
        //     );
        //     $result['success'] = 1;
        // }

        echo json_encode($resp);
    }

    //    public function newnotice_period(){
    // 	$data['id'] = 0;
    // 	$data['dept_code'] = "";
    // 	$data['dept_name'] = "";
    // 	$data['status'] = "";
    // 	$this->NoticePeriod->useDbConfig = $this->Session->read('ds');
    // 	if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
    //            $data_db = $this->NoticePeriod->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
    // 		// debug($data);
    // 		$data = $data_db['NoticePeriod'];
    // 	}
    // 	$this->set(compact("data"));
    // 	$this->layout = null;
    // }
}
