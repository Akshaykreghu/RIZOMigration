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
class ReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'Report';
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
public function getReportCategories()
{
    $this->autoRender = false;

    $this->Features->setDataSource('controldb');

    // Fetch DISTINCT report_list values
    $rows = $this->Features->find('list', [
        'fields' => ['Features.report_list', 'Features.report_list'],
        'conditions' => [
            'Features.feature_key' => 'report',
            'Features.report_list !=' => ''
        ],
        'group' => ['Features.report_list'],
        'order' => ['Features.report_list' => 'ASC']
    ]);

    echo json_encode([
        'success' => true,
        'categories' => array_values($rows)
    ]);
    exit;
}
public function getReportByCategory()
{
    $this->autoRender = false;

    $category = $this->request->query('category');
    if (!$category) {
        echo json_encode(['success' => false, 'message' => 'Category missing']);
        return;
    }

    $this->Features->setDataSource('controldb');
    $this->Plan->setDataSource('controldb');
    $this->CentralUserCredentials->setDataSource('controldb');

    $company_code = strtoupper($this->Session->read('company_code'));

    // ✅ SAME COMPANY LIST
    $not_allowed_companies = array(
        'KWMT','ABSG','MBCT','DRRC','SRTS','MRBS','DJIC','STCL',
        'SHYD','AGNG','ESNP','GTRA','VGNN','AYRK','VGFS','VSFS'
    );

    // Get plan id
    $data = $this->CentralUserCredentials->find('first', [
        'conditions' => ['CentralUserCredentials.company_code' => $company_code],
        'fields'     => ['CentralUserCredentials.plan_id']
    ]);

    $planId = !empty($data) ? $data['CentralUserCredentials']['plan_id'] : null;

    if (!$planId) {
        echo json_encode(['success' => false, 'message' => 'No plan assigned']);
        return;
    }

    // Get features
    $data = $this->Features->find('all', [
        'conditions' => [
            'Features.feature_key' => 'report',
            'Features.report_list' => $category
        ],
        'fields' => [
            'Features.feature_id',
            'Features.feature_name',
            'Features.feature_path',
            'Features.article',
            'Features.display_order'
        ],
        'order' => ['Features.display_order' => 'ASC']
    ]);

    // Enabled features
    $enabled = $this->PlanFeature->find('list', [
        'conditions' => [
            'PlanFeature.plan_id' => $planId,
            'PlanFeature.is_enabled' => 1
        ],
        'fields' => ['PlanFeature.feature_id']
    ]);

    // ✅ PATH MAP (ADD HERE)
    $pathMap = [
        'attendanceReportsNew/hrreportsnew' => 'attendanceReports/hrreports',
        'TrackingReportsNew/hrreports' =>'TrackingReports/hrreports'
    ];

    $result = [];

    foreach ($data as $f) {
        $item = $f['Features'];

        // ✅ APPLY PATH CHANGE
        if (in_array($company_code, $not_allowed_companies)) {
            if (isset($pathMap[$item['feature_path']])) {
                $item['feature_path'] = $pathMap[$item['feature_path']];
            }
        }

        $item['is_enabled'] = in_array($item['feature_id'], $enabled) ? 1 : 0;

        $result[] = $item;
    }

    echo json_encode([
        'success' => true,
        'features' => $result
    ]);
    exit;
}
// public function getReportByCategory()
// {
//     $this->autoRender = false;

//     $category = $this->request->query('category');
//     if (!$category) {
//         echo json_encode(['success' => false, 'message' => 'Category missing']);
//         return;
//     }

//     $this->Features->setDataSource('controldb');
//     $this->Plan->setDataSource('controldb');
//     $this->CentralUserCredentials->setDataSource('controldb');


//     $company_code = $this->Session->read('company_code');
//     $data = $this->CentralUserCredentials->find('first', [
//         'conditions' => ['CentralUserCredentials.company_code' => $company_code],
//         'fields'     => ['CentralUserCredentials.plan_id']
//     ]);
//     $planId = !empty($data) ? $data['CentralUserCredentials']['plan_id'] : null;

//     if (!$planId) {
//         echo json_encode(['success' => false, 'message' => 'No plan assigned']);
//         return;
//     }

//     $data = $this->Features->find('all', [
//         'conditions' => [
//             'Features.feature_key' => 'report',
//             'Features.report_list' => $category
//         ],
//         'fields' => [
//             'Features.feature_id',
//             'Features.feature_name',
//             'Features.feature_path',
//             'Features.article',
//             'Features.display_order'
//         ],
//         'order' => ['Features.display_order' => 'ASC']
//     ]);

 
//     $enabled = $this->PlanFeature->find('list', [
//         'conditions' => [
//             'PlanFeature.plan_id' => $planId,
//             'PlanFeature.is_enabled' => 1
//         ],
//         'fields' => ['PlanFeature.feature_id']
//     ]);

  
//     $result = [];
//     foreach ($data as $f) {
//         $item = $f['Features'];
//         $item['is_enabled'] = in_array($item['feature_id'], $enabled) ? 1 : 0;
//         $result[] = $item;
//     }

//     echo json_encode([
//         'success' => true,
//         'features' => $result
//     ]);
//     exit;
// }
// public function getReportByCategory()
// {
//     $this->autoRender = false;

//     $category = $this->request->query('category');

//     if (!$category) {
//         echo json_encode(['success' => false, 'message' => 'Category missing']);
//         return;
//     }

//     $this->Features->setDataSource('controldb');

//     $data = $this->Features->find('all', [
//         'conditions' => [
//             'Features.feature_key' => 'report',
//             'Features.report_list' => $category
//         ],
//         'fields' => [
//             'Features.feature_id',
//             'Features.feature_name',
//             'Features.feature_path',
//             'Features.article',
//             'Features.display_order'
//         ],
//         'order' => ['Features.display_order' => 'ASC']
//     ]);

//     echo json_encode([
//         'success' => true,
//         'features' => $data
//     ]);
//     exit;
// }
public function getArticleByFeature()
{
    $this->autoRender = false;

    $featureId = $this->request->query('feature_id');

    if (!$featureId) {
        echo json_encode(['success' => false, 'message' => 'Feature ID missing']);
        return;
    }

    $this->Features->setDataSource('controldb');

    $data = $this->Features->find('first', [
        'conditions' => ['Features.feature_id' => $featureId],
        'fields' => [
            'Features.feature_name',
            'Features.description',
            'Features.article'
        ]
    ]);

    echo json_encode([
        'success' => true,
        'feature_name' => $data['Features']['feature_name'],
        'description' => $data['Features']['description'],
        'article' => $data['Features']['article']
    ]);
    exit;
}


}