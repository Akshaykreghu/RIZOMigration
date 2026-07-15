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
class AssetController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Asset';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Assets', 'EmployeeMenu', 'allocate', 'AssetType', 'Units');
    public $components = array('MasterdataManagement');

    public function index()
    {
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $this->AssetType->useDbConfig = $this->Session->read('ds');
        $arr_assets = $this->Assets->find("all", array("fields" => array('Assets.*', 'AssetType.*'), "joins" => array(array(
            'table' => 'asset_types',
            'alias' => 'AssetType',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Assets.Type = AssetType.asset_type_pkey')
        )), "conditions" => array('active' => 1)));
        $this->set("arr_assets", $arr_assets);
        // debug($arr_assets);

    }
    public function listAssetsss()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');



        $resp_emp = array();
        $resp_emp["data"] = array();
        $count = $this->EmployeeDetails->find("count");
        $arr_emp = $this->EmployeeDetails->query("select * from asset_management where active ='1' ");
        foreach ($arr_emp as $key => $value) {
            $resp_emp["data"][$key] = $value["asset_management"];
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }
    public function listAllocates()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //        /debug($arr_request_data);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $length = $arr_request_data['length'];
        $start = $arr_request_data['start'];
        $resp_emp["data"] = array();
        $count = $this->EmployeeDetails->find("count");
        $arr_emp = $this->EmployeeDetails->query("select concat(emp_details.first_name, ' ' ,emp_details.last_name) as name,emp_details.emp_pkey,Assets.asset_name,Assets.status from emp_details left join asset_allocate as Assets on(emp_details.emp_pkey = Assets.emp_fkey) where emp_details.status ='1' LIMIT $start,$length");
        foreach ($arr_emp as $key => $value) {
            $resp_emp["data"][$key] = array_merge($value["0"], $value["emp_details"], $value['Assets']);
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $start;
        echo json_encode($resp_emp);
    }
    public function array_flatten($arr_emp = null)
    {
        $result = array();

        if (!is_array($arr_emp)) {
            $arr_emp = func_get_args();
        }

        foreach ($arr_emp as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }

        return $result;
    }

    //     public function listAllAssets(){ 
    //         $this->autoRender = FALSE;
    //         $arr_request_data = $this->request->data;
    //   // debug($arr_request_data);
    //         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //         $this->Assets->useDbConfig = $this->Session->read('ds');
    //         $this->allocate->useDbConfig = $this->Session->read('ds');
    //         $resp_emp = array();
    //         $page = isset($arr_request_data['page']) ? (int)$arr_request_data['page'] : 1;
    //         $rows = isset($arr_request_data['rows']) ? (int)$arr_request_data['rows'] : 10;
    //         $start = ($page - 1) * $rows;
    //         $length = $rows;
    //         // $ass = $arr_request_data['asset_pkey'];

    //         $param = '';
    //         if(!empty($arr_request_data['search']['value'])){
    //             $param = "and (am.name like '%".$arr_request_data['search']['value']."%' or asset_types.asset_type_name like '%".$arr_request_data['search']['value']."%') ";
    //        }
    //         // $length = $arr_request_data['length'];
    //         // $start = $arr_request_data['start'];
    //         $resp_emp["data"] = array();
    //         $count = $this->Assets->query("select count(*) from asset_management am left join asset_allocate as aa on(am.asset_pkey = aa.asset) left join asset_types on(am.Type = asset_types.asset_type_pkey)where am.active = 1  $param group by am.asset_pkey");
    //         $cnt = count($count);
    //         //$count = $this->Assets->find("count",array("conditions"=>array("active"=>1)));
    //        //debug($count);
    //         $arr_emp = $this->EmployeeDetails->query("select am.*,asset_types.asset_type_name,(select max(asset_state) from asset_allocate where am.asset_pkey = asset_allocate.asset) "
    //                 . "asset_state from asset_management am left join asset_allocate as aa on(am.asset_pkey = aa.asset) left join asset_types on(am.Type = asset_types.asset_type_pkey) where am.active = 1 $param group by am.asset_pkey ORDER BY 1 DESC  LIMIT $start,$length");
    //         // debug($arr_emp);
    // // debug("select am.*,(select max(asset_state) from asset_allocate asset_management.asset_pkey = asset_allocate.asset) where from asset_management am left join asset_allocate as aa on(am.asset_pkey = aa.asset) where am.active = 1  group by am.asset_pkey ORDER BY 1 DESC  LIMIT $start,$length");
    //         $i= $start + 1;
    //         foreach ($arr_emp as $key => $value) {

    // if($value['0']['asset_state'] == 3){
    // $state= "Not Working";
    // }
    //  if($value['0']['asset_state'] == 1){
    // $state= "Good";
    // }
    // if($value['0']['asset_state'] == 0){
    // $state= "Good";
    // }
    // if($value['0']['asset_state'] == 2){
    // $state= "Damage But Working";
    // }
    // // debug($state);
    //           $asset_state = $state;

    //             $value["am"]['si'] = $i;
    //             $value["am"]['asset_state'] = $state;
    //             $value["am"]['type'] = $value["asset_types"]['asset_type_name'];
    //             $i++;
    // //          debug($arr_emp);

    //             $resp_emp["data"][$key] = $value["am"];

    //         }
    //         // debug($resp_emp);
    //         //$resp_emp["total"] = $count;
    //         //$resp_emp["count"] = $count;
    //         $resp_emp["recordsTotal"] = $cnt;
    //         $resp_emp["recordsFiltered"] = $cnt;
    //         echo json_encode($resp_emp);
    // //     }
    // public function listAllAssets()
    // {
    //     $this->autoRender = FALSE;
    //     $arr_request_data = $this->request->data;

    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $this->Assets->useDbConfig = $this->Session->read('ds');
    //     $this->allocate->useDbConfig = $this->Session->read('ds');

    //     $resp_emp = array();
    //     $page = isset($arr_request_data['page']) ? (int)$arr_request_data['page'] : 1;
    //     $rows = isset($arr_request_data['rows']) ? (int)$arr_request_data['rows'] : 10;
    //     $start = ($page - 1) * $rows;
    //     $length = $rows;


    //     $param = '';
    //     if (!empty($arr_request_data['q'])) {
    //         $param = "AND (am.name LIKE '%" . $arr_request_data['q'] . "%' 
    //                OR asset_types.asset_type_name LIKE '%" . $arr_request_data['q'] . "%') ";
    //     }


    //     $count = $this->Assets->query("
    //     SELECT COUNT(DISTINCT am.asset_pkey) as cnt
    //     FROM asset_management am
    //     LEFT JOIN asset_allocate aa ON(am.asset_pkey = aa.asset)
    //     LEFT JOIN asset_types ON(am.Type = asset_types.asset_type_pkey)
    //     WHERE am.active = 1 $param
    // ");
    //     $cnt = $count[0][0]['cnt'];

    //     $arr_emp = $this->EmployeeDetails->query("
    //     SELECT am.*, asset_types.asset_type_name,
    //     (SELECT MAX(asset_state) 
    //      FROM asset_allocate 
    //      WHERE am.asset_pkey = asset_allocate.asset) asset_state
    //     FROM asset_management am
    //     LEFT JOIN asset_allocate aa ON(am.asset_pkey = aa.asset)
    //     LEFT JOIN asset_types ON(am.Type = asset_types.asset_type_pkey)
    //     WHERE am.active = 1 $param
    //     GROUP BY am.asset_pkey
    //     ORDER BY am.asset_pkey DESC
    //     LIMIT $start, $length
    // ");

    //     $rowsData = array();
    //     $i = $start + 1;
    //     foreach ($arr_emp as $key => $value) {

    //         $state = "Good";
    //         if ($value['0']['asset_state'] == 3) {
    //             $state = "Not Working";
    //         }
    //         if ($value['0']['asset_state'] == 2) {
    //             $state = "Damage But Working";
    //         }

    //         $value["am"]['si'] = $i++;
    //         $value["am"]['asset_state'] = $state;
    //         $value["am"]['type'] = $value["asset_types"]['asset_type_name'];

    //         $rowsData[] = $value["am"];
    //     }

    //     $resp_emp["total"] = $cnt;
    //     $resp_emp["rows"]  = $rowsData;

    //     echo json_encode($resp_emp);
    //     // debug($resp_emp);
    // }
// edited by bindu 03-01-26
    // public function listAllAssets()
    // {
    //     $this->autoRender = false;
    //     $arr_request_data = $this->request->data;

    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $this->Assets->useDbConfig = $this->Session->read('ds');
    //     $this->allocate->useDbConfig = $this->Session->read('ds');

    //     $page = isset($arr_request_data['page']) ? (int)$arr_request_data['page'] : 1;
    //     $rows = isset($arr_request_data['rows']) ? (int)$arr_request_data['rows'] : 10;
    //     $start = ($page - 1) * $rows;

    //     // Sanitize and prepare search condition
    //     $param = '';
    //     if (!empty($arr_request_data['q'])) {
    //         $q = addslashes(trim($arr_request_data['q']));
    //         $param = "AND (am.name LIKE '%$q%' OR asset_types.asset_type_name LIKE '%$q%')";
    //     }

    //     // Asset status filter (default: show all if no filter)
    //     $assetStatus = isset($arr_request_data['asset_status']) ? strtolower($arr_request_data['asset_status']) : '';

    //     $statusCond = "";
    //     if ($assetStatus === 'allocated') {
    //         $statusCond = "AND LOWER(am.status) = 'allocated'";
    //     } elseif ($assetStatus === 'returned') {
    //         $statusCond = "AND LOWER(am.status) = 'returned'";
    //     } elseif ($assetStatus === 'not_allocated' || $assetStatus === 'not allocated') {
    //         $statusCond = "AND LOWER(am.status) = 'not allocated'";
    //     }


    //     // Total count query
    //     $count = $this->Assets->query("
    //     SELECT COUNT(DISTINCT am.asset_pkey) as cnt
    //     FROM asset_management am
    //     LEFT JOIN asset_types ON(am.Type = asset_types.asset_type_pkey)
    //     WHERE am.active = 1
    //     $param
    //     $statusCond
    //     ");
    //     $cnt = $count[0][0]['cnt'];
    //     // debug($param);
    //     // debug($statusCond);
    //     // debug($count);

    //     // Data fetch query with limit & offset
    //     $arr_emp = $this->Assets->query("
    //                                         SELECT am.*, asset_types.asset_type_name,
    //                                             (SELECT MAX(asset_state) FROM asset_allocate WHERE am.asset_pkey = asset_allocate.asset) AS asset_state
    //                                         FROM asset_management am
    //                                         LEFT JOIN asset_types ON(am.Type = asset_types.asset_type_pkey)
    //                                         WHERE am.active = 1
    //                                         $param
    //                                         $statusCond
    //                                         GROUP BY am.asset_pkey
    //                                         ORDER BY am.asset_pkey DESC
    //                                         LIMIT $start, $rows
    //                                     ");
    //     // debug($arr_emp);
    //     $rowsData = array();
    //     $i = $start + 1;
    //     foreach ($arr_emp as $key => $value) {
    //         $state = "Good";
    //         if ($value[0]['asset_state'] == 3) {
    //             $state = "Not Working";
    //         } elseif ($value[0]['asset_state'] == 2) {
    //             $state = "Damage But Working";
    //         }

    //         // Edited by Akshay on 2-1-2025
    //         $am = array();

    //         $am = array(
    //             'Type'             => isset($value['am']['Type']) ? $value['am']['Type'] : null,
    //             'active'           => isset($value['am']['active']) ? $value['am']['active'] : null,
    //             'allocated_status' => isset($value['am']['allocated_status']) ? $value['am']['allocated_status'] : null,
    //             'asset_pkey'       => isset($value['am']['asset_pkey']) ? $value['am']['asset_pkey'] : null,
    //             'asset_state'      => isset($state) ? $state : null,
    //             'brand'            => isset($value['am']['brand']) ? $value['am']['brand'] : null,
    //             'condition'        => isset($value['am']['condition']) ? $value['am']['condition'] : null,
    //             'created_time'     => isset($value['am']['created_time']) ? $value['am']['created_time'] : null,
    //             'description'      => isset($value['am']['description']) ? $value['am']['description'] : null,
    //             'emp_fkey'         => isset($value['am']['emp_fkey']) ? $value['am']['emp_fkey'] : null,
    //             'model'            => isset($value['am']['model']) ? $value['am']['model'] : null,
    //             'name'             => isset($value['am']['name']) ? $value['am']['name'] : null,
    //             'serial_no'        => isset($value['am']['serial_no']) ? $value['am']['serial_no'] : null,
    //             'si'               => $i++,
    //             'specifications'   => isset($value['am']['specifications']) ? $value['am']['specifications'] : null,
    //             'status'           => isset($value['am']['status']) ? $value['am']['status'] : null,
    //             'type'             => isset($value['asset_types']['asset_type_name']) ? $value['asset_types']['asset_type_name'] : null,
    //             'value'            => isset($value['am']['value']) ? $value['am']['value'] : null,
    //             'warranty'         => isset($value['am']['warranty']) ? $value['am']['warranty'] : null,
    //             'year'             => isset($value['am']['year']) ? $value['am']['year'] : null,
    //         );

    //         array_walk_recursive($am, function (&$v) {
    //             if (is_string($v)) {
    //                 $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
    //             }
    //         });
    //         $rowsData[] = $am;
    //     }
    //     Configure::write('debug', 0);

    //     $resp_emp = array();
    //     $resp_emp['total'] = (int)$cnt;
    //     $resp_emp['rows']  = $rowsData;

    //     $this->response->type('json');
    //     echo json_encode($resp_emp);
    //     exit;
    // }
// edited by bindu 14-05-26

  public function listAllAssets()
    {
        $this->autoRender = false;
        $arr_request_data = $this->request->data;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $this->allocate->useDbConfig = $this->Session->read('ds');

        $page = isset($arr_request_data['page']) ? (int) $arr_request_data['page'] : 1;
        $rows = isset($arr_request_data['rows']) ? (int) $arr_request_data['rows'] : 10;
        $start = ($page - 1) * $rows;

        // Sanitize and prepare search condition
        $param = '';
        if (!empty($arr_request_data['q'])) {
            $q = addslashes(trim($arr_request_data['q']));
            $param = "AND (am.name LIKE '%$q%' OR asset_types.asset_type_name LIKE '%$q%')";
        }

        // Asset status filter (default: show all if no filter)
        $assetStatus = isset($arr_request_data['asset_status']) ? strtolower($arr_request_data['asset_status']) : '';

        $statusCond = "";
        if ($assetStatus === 'allocated') {
            // Check if it has an active allocation record
            $statusCond = "AND am.asset_pkey IN (SELECT asset FROM asset_allocate WHERE status = 'Allocated' AND active = 1)";
        } elseif ($assetStatus === 'returned') {
            // Check if it is NOT currently allocated but has been returned/allocated before
            $statusCond = "AND am.asset_pkey NOT IN (SELECT asset FROM asset_allocate WHERE status = 'Allocated' AND active = 1) 
                          AND (LOWER(am.status) = 'returned' OR LOWER(am.status) = 'allocated')";
        } elseif ($assetStatus === 'not_allocated' || $assetStatus === 'not allocated') {
            $statusCond = "AND LOWER(am.status) = 'not allocated' AND am.asset_pkey NOT IN (SELECT asset FROM asset_allocate WHERE status = 'Allocated' AND active = 1)";
        }


        // Total count query
        $count = $this->Assets->query("
        SELECT COUNT(DISTINCT am.asset_pkey) as cnt
        FROM asset_management am
        LEFT JOIN asset_types ON(am.Type = asset_types.asset_type_pkey)
        WHERE am.active = 1
        $param
        $statusCond
        ");
        $cnt = $count[0][0]['cnt'];

        // Data fetch query with limit & offset
        $arr_emp = $this->Assets->query("
                                            SELECT am.*, asset_types.asset_type_name,
                                                (SELECT MAX(asset_state) FROM asset_allocate WHERE am.asset_pkey = asset_allocate.asset) AS asset_state,
                                                (SELECT status FROM asset_allocate WHERE asset = am.asset_pkey AND active = 1 ORDER BY allocated_date DESC LIMIT 1) AS current_allocate_status
                                            FROM asset_management am
                                            LEFT JOIN asset_types ON(am.Type = asset_types.asset_type_pkey)
                                            WHERE am.active = 1
                                            $param
                                            $statusCond
                                            GROUP BY am.asset_pkey
                                            ORDER BY am.asset_pkey DESC
                                            LIMIT $start, $rows
                                        ");
        // debug($arr_emp);
        $rowsData = array();
        $i = $start + 1;
        foreach ($arr_emp as $key => $value) {
            $state = "Good";
            if ($value[0]['asset_state'] == 3) {
                $state = "Not Working";
            } elseif ($value[0]['asset_state'] == 2) {
                $state = "Damage But Working";
            }

            // Edited by Akshay on 2-1-2025
            $actualStatus = isset($value['am']['status']) ? $value['am']['status'] : null;
            if (!empty($value[0]['current_allocate_status'])) {
                $actualStatus = $value[0]['current_allocate_status'];
            }

            $am = array(
                'Type' => isset($value['am']['Type']) ? $value['am']['Type'] : null,
                'active' => isset($value['am']['active']) ? $value['am']['active'] : null,
                'allocated_status' => $actualStatus,
                'asset_pkey' => isset($value['am']['asset_pkey']) ? $value['am']['asset_pkey'] : null,
                'asset_state' => isset($state) ? $state : null,
                'brand' => isset($value['am']['brand']) ? $value['am']['brand'] : null,
                'condition' => isset($value['am']['condition']) ? $value['am']['condition'] : null,
                'created_time' => isset($value['am']['created_time']) ? $value['am']['created_time'] : null,
                'description' => isset($value['am']['description']) ? $value['am']['description'] : null,
                'emp_fkey' => isset($value['am']['emp_fkey']) ? $value['am']['emp_fkey'] : null,
                'model' => isset($value['am']['model']) ? $value['am']['model'] : null,
                'name' => isset($value['am']['name']) ? $value['am']['name'] : null,
                'serial_no' => isset($value['am']['serial_no']) ? $value['am']['serial_no'] : null,
                'si' => $i++,
                'specifications' => isset($value['am']['specifications']) ? $value['am']['specifications'] : null,
                'status' => $actualStatus,
                'type' => isset($value['asset_types']['asset_type_name']) ? $value['asset_types']['asset_type_name'] : null,
                'value' => isset($value['am']['value']) ? $value['am']['value'] : null,
                'warranty' => isset($value['am']['warranty']) ? $value['am']['warranty'] : null,
                'year' => isset($value['am']['year']) ? $value['am']['year'] : null,
            );

            array_walk_recursive($am, function (&$v) {
                if (is_string($v)) {
                    $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
                }
            });
            $rowsData[] = $am;
        }
        Configure::write('debug', 0);

        $resp_emp = array();
        $resp_emp['total'] = (int) $cnt;
        $resp_emp['rows'] = $rowsData;

        $this->response->type('json');
        echo json_encode($resp_emp);
        exit;
    }
    // edited by bindu 14-05-26 end

    public function listAllocatesaa()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->allocate->useDbConfig = $this->Session->read('ds');



        $resp_emp = array();
        $resp_emp["data"] = array();
        $count = $this->allocate->find("count");
        $arr_emp = $this->allocate->query("select al.*,am.name as aname,concat(edp.first_name, '', edp.last_name)as name from asset_allocate as al left join emp_details as edp on(edp.emp_pkey = al.emp_fkey) left join asset_management as am on(am.asset_pkey = al.asset_pkey) where al.active= 1");

        foreach ($arr_emp as $key => $value) {
            $resp_emp["data"][$key] = array_merge($value['am'], $value['0'], $value['al']);
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }
    public function addnew_old($pkey = 0)
    {
        $this->set('pkey', $pkey);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->query("select concat(first_name, ' ' ,last_name) as name,emp_pkey from emp_details where status ='1' ");
        $this->set("arr_emp", $arr_emp);
    }



    public function getassets()
    {
        $this->autoRender = false;
        $this->Assets->useDbConfig = $this->Session->read('ds');

        $arr_request_data = $this->request->query;
        //debug($arr_request_data);
        if (isset($arr_request_data['asset'])) {
            $searchkey = $arr_request_data['asset'];
            $filter_condition = 'status not in ("Allocated") and name LIKE "%' . $searchkey . '%"';
        } else {
            $filter_condition = '';
        }
        $arr_users = $this->Assets->find(
            'all',
            array(
                'fields' => 'asset_pkey,concat(name," ",serial_no) as name',
                'conditions' => array(
                    'active' => 1,
                    $filter_condition
                )
            )
        );
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['Assets']) ? array_merge($val['Assets'], $val['0']) : array();
        }
        //  debug($arr_filterresult);
        $arr_filterresult[] = (!empty($arr_filterresult)) ? $arr_filterresult : array("asset_pkey" => "0", "name" => "No Assests Available");
        echo json_encode($arr_filterresult);
    }


    public function getTypes()
    {
        $this->autoRender = false;
        $this->Assets->useDbConfig = $this->Session->read('ds');

        $arr_request_data = $this->request->query;
        //debug($arr_request_data);
        if (isset($arr_request_data['name'])) {
            $searchkey = $arr_request_data['name'];
            $filter_condition = 'Type LIKE "%' . $searchkey . '%"';
        } else {
            $filter_condition = '';
        }
        $arr_users = array();
        $arr_users = $this->Assets->find(
            'all',
            array(
                'fields' => 'DISTINCT Type',
                'conditions' => array(
                    'active' => 1,
                    $filter_condition
                )
            )
        );
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['Assets']) ? $val['Assets'] : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }
    public function addnew($pkey = 0)
    {
        //added by megha asset_type_list on 16/01/2020
        $this->allocate->useDbConfig = $this->Session->read('ds');
        $this->AssetType->useDbConfig = $this->Session->read('ds');

        //end
        $this->set('pkey', $pkey);
        if ($pkey) {
            $this->Assets->useDbConfig = $this->Session->read('ds');
            $arr_emp = $this->Assets->find("all", array(
                "fields" => array('Assets.*', 'AssetType.*'),
                "joins" => array(array(
                    'table' => 'asset_types',
                    'alias' => 'AssetType',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('Assets.Type = AssetType.asset_type_pkey')
                )),
                "conditions" => array("asset_pkey" => $pkey),
            ));
            // debug($arr_emp);
            $this->set("arr_emp", $arr_emp);
            $type = $arr_emp['0']['Assets']['Type'];
            $arr_types = $this->AssetType->query("select * from asset_types where asset_type_pkey != '$type' order by asset_type_name");
        } else {
            $arr_types = $this->AssetType->query("select * from asset_types order by asset_type_name");
        }
        $this->set('arr_types', $arr_types);
    }

    //   public function Allocatenew($pkey = 0)
    //   {
    //       $this->set('pkey', $pkey);
    //       $this->Assets->useDbConfig = $this->Session->read('ds');
    //        $this->allocate->useDbConfig = $this->Session->read('ds');
    //        $conditions = array();
    //     if ($pkey) {
    //         $arr_emp = $this->Assets->find("all", array("conditions" => array("asset_pkey" => $pkey)));
    //         $this->set("arr_emp", $arr_emp);
    //     }

    //       $arr_users = $this->Assets->query("select  asset_types.asset_type_pkey,Assets.asset_pkey,asset_types.asset_type_name,concat(name,' ',serial_no) as name,Assets.status,Assets.Type,(select min(asset_state) from asset_allocate where Assets.asset_pkey = asset_allocate.asset) asset_state  FROM asset_management Assets 
    //              join asset_allocate  allocate on(allocate.asset = Assets.asset_pkey) 
    //              join asset_types   on(asset_types.asset_type_pkey = Assets.Type)
    //             WHERE Assets.active = 1 and allocate.asset_state != 3 and Assets.status !='Allocated' group by asset_types.asset_type_pkey
    //             UNION 
    //             SELECT  asset_types.asset_type_pkey,Assets.asset_pkey,asset_types.asset_type_name,concat(name,' ',serial_no) as name,status,Type,
    //             (select min(asset_state) from asset_allocate   
    //             where Assets.asset_pkey = asset_allocate.asset) asset_state
    //             FROM asset_management Assets join asset_types   on(asset_types.asset_type_pkey = Assets.Type)
    //              WHERE Assets.status = 'Not Allocated'  and 
    //               asset_types.asset_type_pkey not in (select asset_types.asset_type_pkey from asset_management Assets join asset_allocate  allocate on(allocate.asset = Assets.asset_pkey) 
    //              join asset_types   on(asset_types.asset_type_pkey = Assets.Type)
    //             WHERE Assets.active = 1 and allocate.asset_state != 3 and Assets.status !='Allocated') group by asset_types.asset_type_pkey");
    //  // debug($arr_users);
    //     $this->set('arr_users',$arr_users);
    // }
    // edited by bindu 29-08-25
    public function Allocatenew($pkey = 0, $branch = null)
    {
        $this->set('pkey', $pkey);
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $this->allocate->useDbConfig = $this->Session->read('ds');

        if ($pkey) {
            $arr_emp = $this->Assets->find("all", array(
                "conditions" => array("asset_pkey" => $pkey)
            ));
            $this->set("arr_emp", $arr_emp);
        }

        $sql = "
        SELECT  
            asset_types.asset_type_pkey,
            Assets.asset_pkey,
            asset_types.asset_type_name,
            CONCAT(Assets.name,' ',Assets.serial_no) AS name,
            Assets.status,
            Assets.Type,
            (
                SELECT MIN(asset_state) 
                FROM asset_allocate 
                WHERE Assets.asset_pkey = asset_allocate.asset
            ) AS asset_state
        FROM asset_management Assets 
        JOIN asset_allocate allocate ON (allocate.asset = Assets.asset_pkey) 
        JOIN asset_types ON (asset_types.asset_type_pkey = Assets.Type)
        JOIN employee_info Emp ON (Emp.emp_pkey = allocate.emp_fkey)
        WHERE Assets.active = 1 
            AND allocate.asset_state != 3 
            AND Assets.status != 'Allocated'
        ";

        if (!empty($branch) && strtolower($branch) !== 'all') {
            $sql .= " AND Emp.branch_code = '" . $branch . "' ";
        }

        $sql .= " GROUP BY asset_types.asset_type_pkey

        UNION

        SELECT  
            asset_types.asset_type_pkey,
            Assets.asset_pkey,
            asset_types.asset_type_name,
            CONCAT(Assets.name,' ',Assets.serial_no) AS name,
            Assets.status,
            Assets.Type,
            (
                SELECT MIN(asset_state) 
                FROM asset_allocate   
                WHERE Assets.asset_pkey = asset_allocate.asset
            ) AS asset_state
        FROM asset_management Assets 
        JOIN asset_types ON (asset_types.asset_type_pkey = Assets.Type)
        WHERE Assets.status = 'Not Allocated'
            AND asset_types.asset_type_pkey NOT IN (
                SELECT asset_types.asset_type_pkey 
                FROM asset_management Assets 
                JOIN asset_allocate allocate ON (allocate.asset = Assets.asset_pkey) 
                JOIN asset_types ON (asset_types.asset_type_pkey = Assets.Type)
                JOIN employee_info Emp2 ON (Emp2.emp_pkey = allocate.emp_fkey)
                WHERE Assets.active = 1 
                    AND allocate.asset_state != 3 
                    AND Assets.status != 'Allocated'
        ";

        if (!empty($branch) && strtolower($branch) !== 'all') {
            $sql .= " AND Emp2.branch_code = '" . $branch . "' ";
        }

        //     $sql .= ")
        // GROUP BY asset_types.asset_type_pkey";

        //     $arr_users = $this->Assets->query($sql);
        $arr_users = $this->Assets->query("
        SELECT 
            asset_type_pkey,
            asset_type_name
        FROM asset_types
        ORDER BY asset_type_name ASC
        ");

        $this->set('arr_users', $arr_users);


        // $this->set('arr_users', $arr_users);
        $this->set('branch', $branch);
    }
    // edited by bindu 29-08-25 end
    public function edit($edit_pkey = 0)
    {
        $this->allocate->useDbConfig = $this->Session->read('ds');
        $this->Assets->useDbConfig = $this->Session->read('ds');
        //added by amal on 16/08/2019 allocate.description
        $assets = $this->allocate->find(
            "all",
            array(
                "fields" => array("allocate.official_mail,allocate.official_contact,allocate.crm_id,allocate.allocated_ofc_space,"
                    . "allocate.allocated_date,AssetType.asset_type_name,allocate.emp_fkey,allocate.status,allocate.retreived_date,allocate.allocate_pkey,allocate.asset,allocate.description,Assets.name,Assets.Type"),
                "joins" => array(
                    array(
                        'table' => 'asset_management',
                        'alias' => 'Assets',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('Assets.asset_pkey = allocate.asset')
                    ),
                    array(
                        'table' => 'asset_types',
                        'alias' => 'AssetType',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('AssetType.asset_type_pkey = Assets.Type')
                    )
                ),
                "conditions" => array("allocate.allocate_pkey" => $edit_pkey)
            )
        );
        $this->set('edit_pkey', $edit_pkey);
        $this->set('assets', $assets);
        // debug($assets);
        // $filter_condition = 'status not in ("Allocated")';
        // $arr_users = $this->Assets->find(
        //     'all',
        //     array(
        //         'fields' => 'asset_pkey,concat(name," ",serial_no) as name',
        //         'conditions' => array(
        //             'active' => 1,
        //             $filter_condition
        //         )
        //     )
        // );
        // $this->set('arr_users', $arr_users);

        // edited by bindu 08-01-2025
        // Fetch available assets
        // $filter_condition = 'status not in ("Allocated")';
        // $arr_users = $this->Assets->find('all', array(
        //     'fields' => 'asset_pkey,concat(name," ",serial_no) as name',
        //     'conditions' => array(
        //         'active' => 1,
        //         $filter_condition
                
        //     )
        // ));
        $filter_condition = 'Assets.status NOT IN ("Allocated")';

// Fetch asset types
$arr_users_raw = $this->Assets->find('all', [
    'fields' => [
        'asset_types.asset_type_pkey',
        'asset_types.asset_type_name'
    ],
    'joins' => [
        [
            'table' => 'asset_types',
            'alias' => 'asset_types',
            'type'  => 'INNER',
            'conditions' => [
                'asset_types.asset_type_pkey = Assets.Type'
            ]
        ]
    ],
    'conditions' => [
    'Assets.active' => 1,
    'OR' => [
        'Assets.status !=' => 'Allocated',
        'Assets.asset_pkey' => $assets[0]['allocate']['asset']
    ]
],
    'group' => [
        'asset_types.asset_type_pkey',
        'asset_types.asset_type_name'
    ],
    'order' => [
        'asset_types.asset_type_name' => 'ASC'
    ]
]);


$arr_users = [];
$selectedAssetType = !empty($assets[0]['Assets']['Type'])
    ? $assets[0]['Assets']['Type']
    : null;

foreach ($arr_users_raw as $row) {
    if ($row['asset_types']['asset_type_pkey'] == $selectedAssetType) {
        array_unshift($arr_users, $row);
    } else {
        $arr_users[] = $row;
    }
}

$this->set('arr_users', $arr_users);
// edited by bindu 08-01-2025 end
        $this->render('Allocatenew');
    }
    // public function getEmi()
    // {
    //     $this->autoRender = false;
    //     $this->allocate->useDbConfig = $this->Session->read('ds');
    //     $this->Assets->useDbConfig = $this->Session->read('ds');
    //     $arr_form_data = $this->request->data;
    //     $arr_pkey = $arr_form_data['type'];
    //     $arr_asset_names = $this->Assets->query("select name,asset_pkey from asset_management left join asset_allocate as allocate on (asset_management.asset_pkey = allocate.asset) where Type='$arr_pkey' and asset_management.status not in ('Allocated') and asset_pkey not in(select asset from asset_allocate where asset_state = 3) group by asset_management.asset_pkey");
    //     $appnds = '';
    //     foreach ($arr_asset_names as $val) {
    //         $appnds .= '<option value="' . $val['asset_management']['asset_pkey'] . '" >' . $val['asset_management']['name'] . '</option>';
    //     }
    //     echo json_encode(array("success" => 1, "data" => $appnds));
    // }
    public function getEmi()
{
    $this->autoRender = false;

    $this->Assets->useDbConfig = $this->Session->read('ds');

    $type = $this->request->data['type'];

    $arr_asset_names = $this->Assets->query("
        SELECT asset_management.asset_pkey, asset_management.name
        FROM asset_management
        WHERE asset_management.Type = '$type'
         AND asset_management.active = 1
        AND asset_management.status != 'Allocated'
        AND asset_management.asset_pkey NOT IN (
            SELECT asset 
            FROM asset_allocate 
            WHERE asset_state = 3
        )
        ORDER BY asset_management.name ASC
    ");

    $options = '<option value="">Select</option>';

    foreach ($arr_asset_names as $val) {
        $options .= '<option value="'.$val['asset_management']['asset_pkey'].'">'
                  .$val['asset_management']['name'].
                  '</option>';
    }

    echo json_encode([
        "success" => 1,
        "data" => $options
    ]);
}
    public function details($edit_pkey = 0)
    {
        $this->allocate->useDbConfig = $this->Session->read('ds');
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $assets = $this->allocate->find("all", array("fields" => array("EmployeeDetails.first_name,EmployeeDetails.last_name,allocate.allocated_date,allocate.emp_fkey,allocate.status,allocate.retreived_date,allocate.allocate_pkey,allocate.asset,Assets.name,allocate.description,allocate.asset_state"), "joins" => array(
            array(
                'table' => 'asset_management',
                'alias' => 'Assets',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Assets.asset_pkey = allocate.asset')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = allocate.emp_fkey')
            )
        ), "conditions" => array("allocate.asset" => $edit_pkey)));
        //           debug($assets);
        $this->set('edit_pkey', $edit_pkey);
        $this->set('assets', $assets);
        //         debug($assets);
        //$filter_condition = 'allocated_status not in ("Allocated")';
        $arr_users = $this->Assets->find(
            'all',
            array(
                'conditions' => array(
                    'active' => 1,
                    "asset_pkey" => $edit_pkey
                )
            )
        );
        //        debug($arr_users);
        $this->set('arr_users', $arr_users);
    }

    public function save()
    {
        $this->autoRender = false;
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $this->Assets->save($arr_form_data);
    }

    public function release($asset_pkey = 0)
    {
        $this->autoRender = false;
        $this->allocate->useDbConfig = $this->Session->read('ds');
        $dates = date("Y-m-d");
        $releas = $this->allocate->query("update asset_allocate set status = 'Returned',retreived_date = '$dates' where asset = '$asset_pkey' and status = 'Allocated' ");
        $releas = $this->allocate->query("update asset_management set status = 'Returned' where asset_pkey = '$asset_pkey' ");
        echo 1;
    }

    public function Emp($pkey = 0)
    {
        $this->set('pkey', $pkey);
        //        if($pkey)
        //        {
        //            $arr_emp = $this->EmployeeDetails->query("select al.*,am.name as aname,concat(edp.first_name, '', edp.last_name)as name from asset_allocate as al left join emp_details as edp on(edp.emp_pkey = al.emp_fkey) left join asset_management as am on(am.asset_pkey = al.asset_pkey) where al.status= 1 and allocate_pkey = '$pkey' ");
        //        $this->set('arr_emp',$arr_emp);
    }
    public function asset($pkey = 0)
    {
        $this->set('pkey', $pkey);
        //        if($pkey)
        //        {
        //            $arr_emp = $this->EmployeeDetails->query("select al.*,am.name as aname,concat(edp.first_name, '', edp.last_name)as name from asset_allocate as al left join emp_details as edp on(edp.emp_pkey = al.emp_fkey) left join asset_management as am on(am.asset_pkey = al.asset_pkey) where al.status= 1 and allocate_pkey = '$pkey' ");
        //        $this->set('arr_emp',$arr_emp);
    }
    //     public function listitems($pkey = 0)
    //     {
    //     $this->autoRender = false;
    //     $this->allocate->useDbConfig = $this->Session->read('ds');
    //     $assets = $this->allocate->find("all",array("fields"=>array("allocate.allocated_date,allocate.status,allocate.retreived_date,allocate.allocate_pkey,allocate.asset,Assets.name,Assets.specifications,Assets.Type,Assets.serial_no,Assets.model,Assets.brand,Assets.status"),"joins"=>array(array(
    //             'table' => 'asset_management',
    //             'alias' => 'Assets',
    //             'type' => 'LEFT',
    //             'foreignKey' => false,
    //             'conditions' => array('Assets.asset_pkey = allocate.asset')
    //         )),"conditions"=>array("allocate.emp_fkey"=>$pkey,"allocate.status = 'Allocated' ")));
    //     $this->set('assets',$assets);
    //   // debug($assets);
    //     $resp_emp = array();
    //     $resp_emp["data"] = array();
    //     foreach ($assets as $key => $value) {
    //         $resp_emp["data"][$key] = array_merge($value['Assets'],$value['allocate']);
    //     }
    //     //  debug($resp_emp["rows"][$key]);
    //     //$resp_emp["total"] = $count;
    //     echo json_encode($resp_emp);

    //     }
    // edited by bindu 29-08-25
    public function listitems($pkey = 0, $branch = 0)
    {
        $this->autoRender = false;
        $this->allocate->useDbConfig = $this->Session->read('ds');
        // debug($this->request->data);
        if ($this->request->is('post') && isset($this->request->data['branch'])) {
            $branch = trim($this->request->data['branch']);
        }


        $conditions = [
            "allocate.active" => "1"
        ];

        if ($pkey != 0) {
            $conditions["allocate.emp_fkey"] = $pkey;
        }


        $assets = $this->allocate->find("all", [
            "fields" => [
                "allocate.allocated_date",
                "allocate.status",
                // "allocate.retreived_date",
                "allocate.allocate_pkey",
                "allocate.asset",

                "Assets.name",
                "Assets.specifications",
                "Assets.Type",
                // "Assets.serial_no",
                // "Assets.model",
                // "Assets.brand",
                "Assets.status",

                "Emp.EmpName",
                "Emp.emp_id",
                "Emp.branch"
            ],
            "joins" => [
                [
                    'table' => 'asset_management',
                    'alias' => 'Assets',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => [
                        'Assets.asset_pkey = allocate.asset'
                    ]
                ],
                [
                    'table' => 'employee_info',
                    'alias' => 'Emp',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => [
                        'Emp.emp_pkey = allocate.emp_fkey',
                        'Emp.branch_code' => $branch
                    ]
                ]
            ],
            "conditions" => $conditions,
            "order" => ["allocate.allocated_date DESC"]
        ]);
        // debug($assets);
        $resp_emp = ["data" => []];

        foreach ($assets as $key => $value) {

            $resp_emp["data"][$key] = array_merge(
                !empty($value['Assets']) ? $value['Assets'] : [],
                $value['allocate'],
                [
                    "EmpName" => !empty($value['Emp']['EmpName']) ? $value['Emp']['EmpName'] : "N/A",
                    "EmpCode" => !empty($value['Emp']['emp_id']) ? $value['Emp']['emp_id'] : "N/A",
                    "Branch"  => !empty($value['Emp']['branch']) ? $value['Emp']['branch'] : "N/A"
                ]
            );
        }


        echo json_encode($resp_emp);
        // exit;
    }
    // edited by bindu 29-08-25 end

    public function assetsave()
    {
        $this->autoRender = false;
        $this->allocate->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $this->allocate->save($arr_form_data);

        //           $this->allocate->updateAll(
        //     array('asset_state' => "'$MobileNo'")

        // );      
    }
    public function AddnewAsset()
    {
        $this->autoRender = false;
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $this->Assets->save($arr_form_data);
        // debug($arr_form_data);
    }

    public function AllocatenewAsset()
    {
        $this->autoRender = false;
        $this->allocate->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
        $this->allocate->save($arr_form_data);
        $assets = $arr_form_data['asset'];
        $asset_state = $arr_form_data['asset_state'];
        // debug($asset_state);
        $this->allocate->updateAll(['asset_state' => $asset_state], ['asset' => $assets]);
        // allocate->query("update asset_allocate set asset_status = '$asset_state' where asset_pkey = '$assets' ");
        // debug($arr_form_data);
        $status = $arr_form_data['status'];
        $assets = $arr_form_data['asset'];

        // debug($assets);
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $releas = $this->allocate->query("update asset_management set status = '$status' where asset_pkey = '$assets' ");
        //$allocated_status = $arr_form_data['allocated_status'];
        //		  if($allocated_status == 'Allocated')
        //		  {
        //		  $arr1 = $this->Assets->updateAll(
        //    array('status'=>'"Allocated"'),
        //    array("asset_pkey"=>$asset)
        //            );;
        //		  }
        //		  else
        //		  {
        //			  $arr1 = $this->Assets->updateAll(
        //    array('status'=>'"Returned"'),
        //    array("asset_pkey"=>$asset)
        //            );;
        //		  }
    }

    // public function Create_asset()
    // {
    //     $dbConfig = $this->Session->read('ds');
    //     $this->UserCredentials->useDbConfig = $dbConfig;
    //     $this->Units->useDbConfig = $dbConfig;
    //     $this->EmployeeDetails->useDbConfig = $dbConfig;

    //     $cur_emp_key = $this->Session->read("emp_fkey");
    //     $emp_pkey    = $this->Session->read("emp_fkey");
    //     $user_group  = $this->Session->read('user_group');
    //     $company     = $this->Session->read('company_code');

    //     $this->set('user_group', $user_group);

    //     $is_ho = 0;
    //     $this->set('is_ho', $is_ho);

    //     $branchConditions   = ["status" => 1];
    //     $employeeConditions = ["status" => 1];

    //     if ($user_group == 2) {
    //         $payroUser = $this->EmployeeDetails->query("
    //         SELECT emp_proff.payro_priv,
    //                emp_proff.emp_branch,
    //                branches.branch_name 
    //         FROM emp_proff 
    //         JOIN branches ON emp_proff.emp_branch = branches.branch_code 
    //         WHERE emp_proff.emp_fkey = '$cur_emp_key'
    //     ");
    //         $this->set('payroUser', $payroUser);

    //         $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
    //         $is_ho     = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
    //         $this->set('is_ho', $is_ho);

    //         if ($is_ho != 1) {
    //             $branchConditions["branch_code"]   = $is_ho;
    //             $employeeConditions["branch_code"] = $is_ho;
    //         }
    //     }

    //     if ($user_group == 2 && in_array(strtolower($company), ['vgfs', 'vsfs'])) {
    //         $cur_emp_branch_find = $this->EmployeeDetails->find("first", [
    //             "fields"     => ["branch_code"],
    //             "conditions" => ["emp_pkey" => $cur_emp_key, "status" => 1]
    //         ]);

    //         if (!empty($cur_emp_branch_find)) {
    //             $cur_emp_branch                  = $cur_emp_branch_find['EmployeeDetails']['branch_code'];
    //             $branchConditions["branch_code"] = $cur_emp_branch;
    //             $employeeConditions["branch_code"] = $cur_emp_branch;
    //         }
    //     }

    //     if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
    //         $branch                            = $payroUser[0]['emp_proff']['emp_branch'];
    //         $branchConditions["branch_code !="]   = $branch;
    //         $employeeConditions["branch_code !="] = $branch;
    //     }

    //     $arr_branches  = $this->Units->find("all", ["conditions" => $branchConditions]);
    //     $arr_employees = $this->EmployeeDetails->find("all", [
    //         "order"      => ["emp_pkey DESC"],
    //         "conditions" => $employeeConditions
    //     ]);

    //     $this->set("arr_branches", $arr_branches);
    //     $this->set("arr_employees", $arr_employees);
    // }


    // edited by bindu 18-12-2025
    public function Create_asset()
    {
        $dbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $dbConfig;
        $this->Units->useDbConfig = $dbConfig;
        $this->EmployeeDetails->useDbConfig = $dbConfig;

        $cur_emp_key = $this->Session->read("emp_fkey");
        $emp_pkey    = $this->Session->read("emp_fkey");
        $user_group  = $this->Session->read('user_group');
        $company     = $this->Session->read('company_code');

        $this->set('user_group', $user_group);


        $is_ho = 0;
        $this->set('is_ho', $is_ho);


        $branchConditions   = ["status" => 1];
        $employeeConditions = ["status" => 1];


        $arr_branches  = $this->Units->find("all", ["conditions" => $branchConditions]);
        $arr_employees = $this->EmployeeDetails->find("all", [
            "order"      => ["emp_pkey DESC"],
            "conditions" => $employeeConditions
        ]);

        $this->set("arr_branches", $arr_branches);
        $this->set("arr_employees", $arr_employees);
    }
    // edited by bindu 18-12-2025 end

    public function Company_asset() {}
     public function Create_asset_new()
    {
        $emp_pkey = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        $user_id_session = $this->Session->read('user_id');
        $user_id = !empty($user_id_session) ? $user_id_session : $emp_pkey;
        $feature_id = $this->Session->read('current_feature_id');
        $company_code = $this->Session->read('company_code');

        $context = $this->MasterdataManagement->getFeatureAccessContext();

        if ($user_group == 1) {
            $branches = $this->MasterdataManagement->getBranchesForAll();
        } else if ($user_group == 2 && $context['has_access']) {
            if ($context['is_hierarchy']) {
                $branches = $this->MasterdataManagement->getHierarchyBranches($emp_pkey);
            } else {
                $branches = $this->MasterdataManagement->getAllocatedBranches($user_id, $feature_id);
            }
        } else {
            $branches = $this->MasterdataManagement->getOwnBranch($emp_pkey);
        }

        $branch_array = [];
        if (!empty($branches)) {
            foreach ($branches as $b) {
                $obj = new stdClass();
                $obj->id = $b['b']['branch_code'];
                $obj->text = $b['b']['branch_name'];
                $branch_array[] = $obj;
            }
        }

        $this->set('arr_branches', $branch_array);
        // $this->set('is_ho', ($user_group == 1 || ($user_group == 2 && count($branch_array) > 1)) ? 1 : 0);
        $this->set('user_group', $user_group);
    }

     public function getEmployeesByBranch()
    {
        $this->autoRender = false;
        $branch = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : '0';
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $context = $this->MasterdataManagement->getFeatureAccessContext();

       if ($user_group == 1) {
    $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, 0);

} else if ($context['is_hierarchy']) {
    $employees = $this->MasterdataManagement->getHierarchyEmployeesByBranch($emp_pkey, $branch);

} else {
    $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, $emp_pkey);
}

        $result = [];
        if (!empty($employees)) {
            foreach ($employees as $e) {
                $obj = new stdClass();
                $obj->id = $e['e']['id'];
                $obj->text = $e[0]['text'];
                $result[] = $obj;
            }
        }

        // Handle both simple array (Company_asset) and Select2 object (Create_asset)
        if (isset($_REQUEST['page'])) {
            echo json_encode([
                "items" => $result,
                "total_count" => count($result)
            ]);
        } else {
            echo json_encode($result);
        }
    }
    // edited by bindu 18-12-2025 end
    public function Company_asset_new()
    {
        $dbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $dbConfig;
        $this->EmployeeDetails->useDbConfig = $dbConfig;

        $emp_pkey = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        $feature_id = $this->Session->read('current_feature_id');

        $is_hierarchy = 'N';
        if ($user_group == 1) {
            $branches = $this->Units->find("all", [
                "conditions" => ["status" => 1],
                "order" => "branch_name"
            ]);
            $this->set('is_ho', 1);
        } else {
            $access = $this->EmployeeDetails->query("
                SELECT is_hierarchy FROM user_feature_branch_access 
                WHERE user_fkey = '$emp_pkey' AND feature_fkey = '$feature_id' AND active = 'Y' LIMIT 1
            ");
            if (!empty($access)) {
                $is_hierarchy = $access[0]['user_feature_branch_access']['is_hierarchy'];
            }

            if ($is_hierarchy == 'Y') {
                $branches = $this->Units->find("all", [
                    "joins" => [
                        [
                            "table" => "emp_details",
                            "alias" => "Emp",
                            "type" => "INNER",
                            "conditions" => ["Units.branch_code = Emp.branch_code"]
                        ],
                        [
                            "table" => "emp_proff",
                            "alias" => "Proff",
                            "type" => "INNER",
                            "conditions" => ["Emp.emp_pkey = Proff.emp_fkey", "Proff.attr1" => $emp_pkey]
                        ]
                    ],
                    "conditions" => ["Units.status" => 1],
                    "fields" => ["DISTINCT Units.branch_code", "Units.branch_name"],
                    "order" => "Units.branch_name"
                ]);
            } else {
                $branches = $this->Units->find("all", [
                    "joins" => [
                        [
                            "table" => "user_feature_branch_access",
                            "alias" => "Access",
                            "type" => "INNER",
                            "conditions" => [
                                "Units.branch_code = Access.branch_fkey",
                                "Access.user_fkey" => $emp_pkey,
                                "Access.feature_fkey" => $feature_id,
                                "Access.active" => 'Y'
                            ]
                        ]
                    ],
                    "conditions" => ["Units.status" => 1],
                    "order" => "Units.branch_name"
                ]);
            }
            $this->set('is_ho', (count($branches) > 1) ? 1 : 0);
        }

        $branch_array = [];
        if (!empty($branches)) {
            foreach ($branches as $b) {
                $obj = new stdClass();
                $obj->id = $b['Units']['branch_code'];
                $obj->text = $b['Units']['branch_name'];
                $branch_array[] = $obj;
            }
        }

        $this->set('arr_branches', $branch_array);
        $this->set('is_ho', ($user_group == 1 || ($user_group == 2 && count($branch_array) > 1)) ? 1 : 0);
        $this->set('user_group', $user_group);
    }
    
}
