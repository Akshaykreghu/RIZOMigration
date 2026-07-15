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
class ItemSpecificationController extends AppController {

    public $uses = array('Workstatus', 'ItemSpecification');

    /* b
     * Lists workstatus Here
     */

    public function index() {
        
    }

    public function specification($specification_pkey = 0) {
        //  debug($specification_pkey);
		if($specification_pkey){
				
			$result = $this->ItemSpecification->query("select * from item_specification where specification_pkey = '$specification_pkey'");

			$this->set('result', $result);
		}
    }

    public function delete($user_pkey = 0) {
        $this->autoRender = FALSE;
		$this->ItemSpecification->useDbConfig = $this->Session->read('ds');
        //debug($user_pkey);
        if ($user_pkey != 0) {
            $this->ItemSpecification->updateAll(array('status' => 0), array('specification_pkey' => $user_pkey));
            echo json_encode(array('msg' => 'master deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'master deletion failed!'));
        }
    }

    public function save() {

        $this->autoRender = FALSE;
        $this->layout = null;
		$this->ItemSpecification->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;

        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $user = $this->Session->read('user_name');
        if ($arr_form_data['specification_pkey'] != '') {
            $arr_form_data['modified_by'] = $this->Session->read('user_name');
            $arr_form_data['modified_date'] = date('Y-m-d');
        }

        $result = $this->ItemSpecification->save($arr_form_data);
        //debug($result);                    $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Employee Added successfully";
        echo json_encode($resp);
    }

    public function listmaster() {
        $this->autoRender = false;

        $this->ItemSpecification->useDbConfig = $this->Session->read('ds');
        $result = $this->ItemSpecification->find("all", array(
            'conditions' => array('ItemSpecification.status' => 1)
        ));
        //  debug($result);
        $rows = array();
        foreach ($result as $key => $val) {
            $rows[] = $val["ItemSpecification"];
        }

        $resp_data["rows"] = $rows;




        echo json_encode($resp_data);
    }

}
