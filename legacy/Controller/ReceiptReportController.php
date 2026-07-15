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
class ReceiptReportController extends AppController {

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Accountreceivable', 'Site', 'Stages', 'Contacts', 'Location', 'Worktype', 'Usermanagement');
    public $helpers = array('PhpExcel');

    /*
     * List Locations Here
     */

    public function index() {
        $arr_sites = $this->Site->find('all', array('conditions' => array('status' => 1)));
        $this->set('arr_sites', $arr_sites);
        $arr_user = $this->Usermanagement->find('all', array('conditions' => array('active' => 1)));
        $this->set('arr_user', $arr_user);
        $arr_contact = $this->Contacts->find('all', array('conditions' => array('status' => 1)));
        $this->set('arr_contact', $arr_contact);
    }

    public function printgenerateinvoicereport() {
       
        $arr_report_criteria = $this->request->data;
        //$user = $arr_report_criteria['user_id'];
        $r=$arr_report_criteria['site_name'];
        //debug($r);
        if($arr_report_criteria['contact_name'] =="All")
        {
            $contct = "";
        }
        else
        {
            $contact = $arr_report_criteria['contact_name'];
            $contct = "AND `Accountreceivable`.`contact_id` = '$contact' ";
        }
        $originalDate = isset($arr_report_criteria['sdate']) ? $arr_report_criteria['sdate'] : '';
        $newDate = date("Y-m-d", strtotime($originalDate));
        $arr_report_criteria['sdate'] = $newDate;
        $originalDate1 = isset($arr_report_criteria['edate']) ? $arr_report_criteria['edate'] : '';
        $newDate1 = date("Y-m-d", strtotime($originalDate1));
        $arr_report_criteria['edate'] = $newDate1;
        if($arr_report_criteria['site_name'] == "All")
        {
            $arr_pkey = $this->Site->find("all");
        }
        else   
        {
            $arr_pkey = $this->Site->find("all", array('conditions' => array('Site.site_name' => $arr_report_criteria['site_name']))); 
        }

        $arr_pk = $arr_pkey[0]['Site']['site_pkey'];
        
         if($arr_report_criteria['site_name'] =="All")
        {
            $sites = "";
        }
        else
        {
            $sites = "AND `Accountreceivable`.`site_fkey` = '$arr_pk' ";
        }
        
        
        $arr_receipts = $this->Accountreceivable->query("SELECT `Accountreceivable`.*, `Stages`.*, `Site`.*, `Contacts`.*, `Location`.*, `Worktype`.*, `Usermanagement`.* "
                . "FROM `acct_receivables` AS `Accountreceivable` "
                . "INNER JOIN `site` AS `Site` ON (`Accountreceivable`.`site_fkey` = `Site`.`site_pkey`) "
                . "LEFT JOIN `stage` AS `Stages` ON (`Accountreceivable`.`stage_fkey` = `Stages`.`stages_pkey`)"
                . " INNER JOIN `contacts` AS `Contacts` ON (`Accountreceivable`.`contact_id` = `Contacts`.`contact_id`) "
                . "INNER JOIN `locations` AS `Location` ON (`Site`.`location_id` = `Location`.`location_id`) "
                . "INNER JOIN `work_type` AS `Worktype` ON (`Site`.`work_type_id` = `Worktype`.`work_type_id`)"
                . " INNER JOIN `user_management` AS `Usermanagement` ON (`Accountreceivable`.`created_by` = `Usermanagement`.`user_pkey`)"
                . " WHERE `Accountreceivable`.`date_recipt` BETWEEN '$newDate' AND '$newDate1' "
                . "$sites $contct "
                . "AND `Accountreceivable`.`status` = 1  and site.status = '1' ");
        
//        $arr_receipts = $this->Accountreceivable->find('all', array(
//            'fields' => 'Accountresceivable.*,Stages.*,Site.*,Contacts.*,Location.*,Worktype.*,Usermanagement.*',
//            'conditions' => array(array('Accountreceivable.date_recipt BETWEEN "' . $arr_report_criteria['sdate'] . '" AND "' . $arr_report_criteria['edate'] . '"'), array('Accountreceivable.site_fkey' => $arr_pk), array('Accountreceivable.contact_id' => $contact), array('Accountreceivable.status' => 1)),
//            //'conditions' => array(array('Accountreceivable.date_recipt BETWEEN "' . $arr_report_criteria['sdate'] . '" AND "' . $arr_report_criteria['edate'] . '"'),array('Accountreceivable.site_fkey'=>$arr_pk),array('Accountreceivable.created_by'=>$user),array('Accountreceivable.contact_id'=>$contact),array('Accountreceivable.status' => 1)) ,
//            'joins' => array(
//                array(
//                    'table' => 'site',
//                    'alias' => 'Site',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Accountreceivable.site_fkey = Site.site_pkey'
//                    )
//                ),
//                array(
//                    'table' => 'stage',
//                    'alias' => 'Stages',
//                    'type' => 'LEFT',
//                    'conditions' => array(
//                        'Accountreceivable.stage_fkey = Stages.stages_pkey'
//                    )
//                ),
//                array(
//                    'table' => 'contacts',
//                    'alias' => 'Contacts',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Accountreceivable.contact_id = Contacts.contact_id'
//                    )
//                ),
//                array(
//                    'table' => 'locations',
//                    'alias' => 'Location',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Site.location_id = Location.location_id'
//                    )
//                ),
//                array(
//                    'table' => 'work_type',
//                    'alias' => 'Worktype',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Site.work_type_id = Worktype.work_type_id'
//                    )
//                ),
//                array(
//                    'table' => 'user_management',
//                    'alias' => 'Usermanagement',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Accountreceivable.created_by = Usermanagement.user_pkey'
//                    )
//                ),
//            ),
//                )
//        );
        //debug($arr_receipts);
        $arr_reportdata = array();
        foreach ($arr_receipts as $value) {
            if (isset($value['Site']['site_pkey']) && $value['Site']['site_pkey'] != '' && $value['Site']['site_pkey'] != 0) {
                $site_pkey = $value['Site']['site_pkey'];
                $value['Site']['location_details'] = $value['Location']['location_name'];
                $value['Site']['worktype_details'] = $value['Worktype']['type_name'];
                $value['Site']['user_details'] = $value['Usermanagement']['first_name'] . " " . $value['Usermanagement']['last_name'];
                $arr_reportdata[$site_pkey]['site_details'] = $value['Site'];
                $arr_site_reciept = $value['Accountreceivable'];
                $arr_site_reciept['stage'] = $value['Stages']['stage_short'] . '-' . $value['Stages']['stage_desc'];
                // $arr_site_reciept['costitem'] = $value['Costitem']['item_short'];
                $arr_site_reciept['contact'] = $value['Contacts']['first_name'] . " " . $value['Contacts']['last_name'];

                $arr_reportdata[$site_pkey]['site_reciepts'][] = $arr_site_reciept;
            }
        }
      $this->set('arr_reportdata', $arr_reportdata);
      
        $this->layout = null;
    }

    public function generatereceiptreport() {


        $arr_report_criteria = $this->request->data;
        //$user = $arr_report_criteria['user_id'];
        $r=$arr_report_criteria['site_id'];
        //debug($r);
        if($arr_report_criteria['contact_id'] =="All")
        {
            $contct = "";
        }
        else
        {
            $contact = $arr_report_criteria['contact_id'];
            $contct = "AND `Accountreceivable`.`contact_id` = '$contact' ";
        }
        $originalDate = isset($arr_report_criteria['sdate']) ? $arr_report_criteria['sdate'] : '';
        $newDate = date("Y-m-d", strtotime($originalDate));
        $arr_report_criteria['sdate'] = $newDate;
        $originalDate1 = isset($arr_report_criteria['edate']) ? $arr_report_criteria['edate'] : '';
        $newDate1 = date("Y-m-d", strtotime($originalDate1));
        $arr_report_criteria['edate'] = $newDate1;
        if($arr_report_criteria['site_id'] == "All")
        {
            $arr_pkey = $this->Site->find("all", array('conditions' => array('Site.status' => 1)));
        }
        else   
        {
            $arr_pkey = $this->Site->find("all", array('conditions' => array('Site.site_name' => $arr_report_criteria['site_id']))); 
        }

        $arr_pk = $arr_pkey[0]['Site']['site_pkey'];
        
         if($arr_report_criteria['site_id'] =="All")
        {
            $sites = "";
        }
        else
        {
            $sites = "AND `Accountreceivable`.`site_fkey` = '$arr_pk' ";
        }
        
        
        $arr_receipts = $this->Accountreceivable->query("SELECT `Accountreceivable`.*, `Stages`.*, `Site`.*, `Contacts`.*, `Location`.*, `Worktype`.*, `Usermanagement`.* "
                . "FROM `acct_receivables` AS `Accountreceivable` "
                . "INNER JOIN `site` AS `Site` ON (`Accountreceivable`.`site_fkey` = `Site`.`site_pkey`) "
                . "LEFT JOIN `stage` AS `Stages` ON (`Accountreceivable`.`stage_fkey` = `Stages`.`stages_pkey`)"
                . " INNER JOIN `contacts` AS `Contacts` ON (`Accountreceivable`.`contact_id` = `Contacts`.`contact_id`) "
                . "INNER JOIN `locations` AS `Location` ON (`Site`.`location_id` = `Location`.`location_id`) "
                . "INNER JOIN `work_type` AS `Worktype` ON (`Site`.`work_type_id` = `Worktype`.`work_type_id`)"
                . " INNER JOIN `user_management` AS `Usermanagement` ON (`Accountreceivable`.`created_by` = `Usermanagement`.`user_pkey`)"
                . " WHERE `Accountreceivable`.`date_recipt` BETWEEN '$newDate' AND '$newDate1' "
                . "$sites $contct "
                . "AND `Accountreceivable`.`status` = 1 and site.status = '1'");
        
//        $arr_receipts = $this->Accountreceivable->find('all', array(
//            'fields' => 'Accountresceivable.*,Stages.*,Site.*,Contacts.*,Location.*,Worktype.*,Usermanagement.*',
//            'conditions' => array(array('Accountreceivable.date_recipt BETWEEN "' . $arr_report_criteria['sdate'] . '" AND "' . $arr_report_criteria['edate'] . '"'), array('Accountreceivable.site_fkey' => $arr_pk), array('Accountreceivable.contact_id' => $contact), array('Accountreceivable.status' => 1)),
//            //'conditions' => array(array('Accountreceivable.date_recipt BETWEEN "' . $arr_report_criteria['sdate'] . '" AND "' . $arr_report_criteria['edate'] . '"'),array('Accountreceivable.site_fkey'=>$arr_pk),array('Accountreceivable.created_by'=>$user),array('Accountreceivable.contact_id'=>$contact),array('Accountreceivable.status' => 1)) ,
//            'joins' => array(
//                array(
//                    'table' => 'site',
//                    'alias' => 'Site',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Accountreceivable.site_fkey = Site.site_pkey'
//                    )
//                ),
//                array(
//                    'table' => 'stage',
//                    'alias' => 'Stages',
//                    'type' => 'LEFT',
//                    'conditions' => array(
//                        'Accountreceivable.stage_fkey = Stages.stages_pkey'
//                    )
//                ),
//                array(
//                    'table' => 'contacts',
//                    'alias' => 'Contacts',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Accountreceivable.contact_id = Contacts.contact_id'
//                    )
//                ),
//                array(
//                    'table' => 'locations',
//                    'alias' => 'Location',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Site.location_id = Location.location_id'
//                    )
//                ),
//                array(
//                    'table' => 'work_type',
//                    'alias' => 'Worktype',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Site.work_type_id = Worktype.work_type_id'
//                    )
//                ),
//                array(
//                    'table' => 'user_management',
//                    'alias' => 'Usermanagement',
//                    'type' => 'INNER',
//                    'conditions' => array(
//                        'Accountreceivable.created_by = Usermanagement.user_pkey'
//                    )
//                ),
//            ),
//                )
//        );
        //debug($arr_receipts);
        $arr_reportdata = array();
        foreach ($arr_receipts as $value) {
            if (isset($value['Site']['site_pkey']) && $value['Site']['site_pkey'] != '' && $value['Site']['site_pkey'] != 0) {
                $site_pkey = $value['Site']['site_pkey'];
                $value['Site']['location_details'] = $value['Location']['location_name'];
                $value['Site']['worktype_details'] = $value['Worktype']['type_name'];
                $value['Site']['user_details'] = $value['Usermanagement']['first_name'] . " " . $value['Usermanagement']['last_name'];
                $arr_reportdata[$site_pkey]['site_details'] = $value['Site'];
                $arr_site_reciept = $value['Accountreceivable'];
                $arr_site_reciept['stage'] = $value['Stages']['stage_short'] . '-' . $value['Stages']['stage_desc'];
                // $arr_site_reciept['costitem'] = $value['Costitem']['item_short'];
                $arr_site_reciept['contact'] = $value['Contacts']['first_name'] . " " . $value['Contacts']['last_name'];

                $arr_reportdata[$site_pkey]['site_reciepts'][] = $arr_site_reciept;
            }
        }
        $this->set('arr_reportdata', $arr_reportdata);
        //debug($arr_reportdata);
    }

    /*public function getuserssbysite($skey = 0) {
        $arr_data = $this->request->data;
        $this->autoRender = false;
        $arr_users = $this->Accountreceivable->query("select distinct created_by from acct_receivables where site_fkey=$skey");
        foreach ($arr_users as $data) {
            $a = $data['acct_receivables']['created_by'];
            $arr_username = $this->Usermanagement->query("select distinct user_pkey as uid,CONCAT(first_name, ' ', last_name) as name  from user_management where user_pkey=$a");
            debug($arr_username);
            $str_use_options_html = '';
            foreach ($arr_username as $value) {
                $upkey = $value['user_management']['uid'];
                $username = $value[0]['name'];
                $str_use_options_html .= '<option value="' . $upkey . '">' . $username . '</option>';
            }
            echo $str_use_options_html;
        }
    }*/

    public function getcontactsbysite($skey = 0) {
      //  debug($skey);
        if($skey =='All')
        {
            $where = '';
        }
        else
        {
            $where ="where site_fkey=$skey ";
        }
        $arr_data = $this->request->data;
        $this->autoRender = false;
        $arr_contacts = $this->Accountreceivable->query("select distinct contact_id from acct_receivables $where ");
        echo $str_contact_options_html .= '<option value="All">All</option>';
        foreach ($arr_contacts as $data) {
            $cid = $data['acct_receivables']['contact_id'];
            $arr_contactname = $this->Contacts->query("select contact_id as cid ,CONCAT(first_name, ' ', last_name) as name  from contacts where contact_id=$cid  ");
            $str_contact_options_html = '';
            
            foreach ($arr_contactname as $value) {
                $contactid = $value['contacts']['cid'];
                $contactname = $value[0]['name'];
                $str_contact_options_html .= '<option value="' . $contactid . '">' . $contactname . '</option>';
            }
            echo $str_contact_options_html;
        }
    }

}
