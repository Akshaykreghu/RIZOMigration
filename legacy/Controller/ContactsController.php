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
class ContactsController extends AppController {

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Contacts','Organization');
    
    /*
     * List Locations Here
     */
    public function index(){
        
    }
    
    /*
     * Add / edit locations form
     */
    public function addeditcontacts($contact_id=0){
        if($contact_id == 0){
            $title = 'Add Customer/Vendor';
        }else{
            $title = 'Edit Customer/Vendor';
        }
        $this->set('title', $title);
        $this->Contacts-> useDbConfig = $this -> Session -> read('ds');
        $this->layout = NULL;
        $arr_contacts = $this->Contacts->find('all', array(
                            'conditions' => array('Contacts.contact_id' => $contact_id)
                        ));
        $this->set('contact_id',$contact_id);
        $this->set('arr_contacts',$arr_contacts);
    }
    
    /*
     * Save here
     */
    public function save(){
        $this->autoRender = false;
		$this->Contacts-> useDbConfig = $this -> Session -> read('ds');
		$this->Organization-> useDbConfig = $this -> Session -> read('ds');
        $arr_form_data = $this->request->data;
        $org_info=$this->Organization->find("first");
        $arr_form_data['organization_id'] = $org_info['Organization']['organization_id'];
        $this->Contacts->save($arr_form_data);
        echo json_encode(array('msg'=>'Contacts saved successfully'));
    }
    //EDITED BY MEGHA ON 18/02/2020 FILTER DATA RELATION SHIP AND SEARCH OPTION
    function listcontacts()
    {
        $this->Contacts-> useDbConfig = $this -> Session -> read('ds');
        $resp_data = array();
        $this->autoRender = false;
        $arr_data = $this->request->data;
       //debug($arr_data);
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $contact = isset($arr_data['emp']) ? $arr_data['emp'] : '';
        $relationship = isset($arr_data['relationship']) ? $arr_data['relationship'] : '';
        //debug($contact);
        $sort = isset($arr_data['sort'])?$arr_data['sort']:'contact_id';
        $order = isset($arr_data['order'])?$arr_data['order']:'desc';
        
        if($sort != '' && $order !== ''){
            $arr_order = array($sort.' '.$order);
        }else{
            $arr_order = array();
        }
        $conditions = array("Contacts.status" => 1);
        if(isset($contact)){
            $conditions[] = "(first_name like '%".$contact."%' OR company_name like '%".$contact."%'  OR email like '%".$contact."%' OR phone like '%".$contact."%' OR city like '%".$contact."%' OR relationship like '%".$contact."%')";
        }
        if(isset($relationship)){
            $conditions[] = "(relationship like '%".$relationship."%') ";
        }
        // and relationship != ''
        $totalcount = $this->Contacts->find("count",array(
                'conditions' => $conditions 
            )
                );
        $arr_contacts = $this->Contacts->find("all",array(
                'order'=>$arr_order,
                'conditions' => $conditions ,
                'order'=>array($sort=>$order),
                'limit'=>intval($limit),
                'offset'=>intval($ofst)
            )
                );
       
        $rows = array();
        foreach($arr_contacts as $key=>$val){
            $rows[] = $val["Contacts"];
        }
        
          $resp_data["total"] = $totalcount;
          $resp_data["rows"] = $rows;
        
        
      
        
        echo json_encode($resp_data);
    }
    public function deletecontacts($contact_id=0){
        $this->autoRender = false;
		$this->Contacts-> useDbConfig = $this -> Session -> read('ds');
        if($contact_id != 0){
            $this->Contacts->updateAll(array('status'=>0),array('contact_id'=>$contact_id));
            echo json_encode(array('msg' => 'Contact deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Contact deletion failed!'));
        }
    }
}
