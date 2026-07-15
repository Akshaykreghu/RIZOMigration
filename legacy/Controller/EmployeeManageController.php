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
class EmployeeManageController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EmployeeManage';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Menu','LeavePolicyGroup', 'CentralControl', 'Banks','UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'EmployeeGrossDetails', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation', 'DbConfig', 'ReportAudit','FinancialYear','EmployeeTaxsalsumNew','EmployeeTaxsalsum','Gender','Plan', 'Features', 'PlanFeature', 'CentralUserCredentials');
    public $components = array('MasterdataManagement');

    public function index(){
        
    }

       public function getEmployeeFeatures()
{
    $this->autoRender = false;

    $this->Plan->setDataSource('controldb');
    $this->CentralUserCredentials->setDataSource('controldb');

    // Get user plan id
    $company_code = $this->Session->read('company_code');
    $data = $this->CentralUserCredentials->find('first', [
        'conditions' => ['CentralUserCredentials.company_code' => $company_code],
        'fields'     => ['CentralUserCredentials.plan_id']
    ]);
    $planId = !empty($data) ? $data['CentralUserCredentials']['plan_id'] : null;

    if (!$planId) {
        echo json_encode(['error' => 'No plan assigned']);
        exit;
    }

    // Get all payroll features
   $allFeatures = $this->Features->find('all', [
    'conditions' => [
        'Features.feature_key' => 'employee',
        'Features.is_common' => 0   // show only is_common = 0
    ],
    'fields' => [
        'Features.feature_id',
        'Features.feature_name',
        'Features.feature_path',
        'Features.description',
        'Features.article',
        'Features.is_common',
        'Features.display_order'
    ],
    'order' => ['Features.display_order' => 'ASC']
]);
 
    // Get enabled features for this plan
    $enabled = $this->PlanFeature->find('list', [
        'conditions' => [
            'PlanFeature.plan_id' => $planId,
            'PlanFeature.is_enabled' => 1
        ],
        'fields' => ['PlanFeature.feature_id']
    ]);

    $result = [];
    // foreach ($allFeatures as $f) {
    //     $item = $f['Features'];
    //     $item['is_enabled'] = in_array($item['feature_id'], $enabled) ? 1 : 0;
    //     $result[] = $item;
    // }
 $company_code = strtoupper($this->Session->read('company_code'));

$not_allowed_companies = [
    'KWMT','ABSG','MBCT','DRRC','SRTS','MRBS','DJIC','STCL',
    'SHYD','AGNG','ESNP','GTRA','VGNN','AYRK','VGFS','VSFS'
];

$result = [];

foreach ($allFeatures as $f) {

    $item = $f['Features'];

    // Apply only if company is in the restricted list
    if (in_array($company_code, $not_allowed_companies)) {

        // Modify User Access path
        if (!empty($item['feature_path']) && $item['feature_path'] === 'UserAccess/indexnew') {
            $item['feature_path'] = 'UserAccess';
        }

        // Modify Asset Creation path
        if (!empty($item['feature_path']) && $item['feature_path'] === 'Asset/Create_asset_new') {
            $item['feature_path'] = 'Asset/Create_asset';
        }
    }

    // Enable or disable features
    $item['is_enabled'] = in_array($item['feature_id'], $enabled) ? 1 : 0;

    $result[] = $item;
}
    header('Content-Type: application/json');
    echo json_encode([
        'plan_id'  => $planId,
        'features' => $result
    ]);
    exit;
}

}