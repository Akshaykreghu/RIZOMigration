<?php

class StockmanagementController extends AppController
{
    public $name = 'Stockmanagement';
    public $uses = array('CentralControl', 'EmpDocument', 'EmpFam', 'Education', 'WorkExperience', 'EmployeeJoin', 'EmployeeSalaryStructure', 'EmployeeConfig', 'Family', 'passport', 'Promotion', 'NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmpAlterationDetails', 'ReportCriterias', 'SalaryIncrement', 'SalaryIncrementDetails', 'ComponentIncrement', 'EditPunches','Plan', 'Features', 'PlanFeature', 'CentralUserCredentials'); // Edited by Akshay on 17-4-2025
    //end
    public $components = array('MasterdataManagement');

    public function index() {

        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        
        $this->set('arr_branches', $arr_branches);

    }
       public function getAttendanceFeatures()
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
        'conditions' => ['Features.feature_key' => 'Stockmanagement'],
        'fields' => [
            'Features.feature_id',
            'Features.feature_name',
            'Features.feature_path',
            'Features.description',
            'Features.article',
            'Features.display_order'  // Add the new field
    ],
    'order' => ['Features.display_order' => 'ASC']  // Order by display_order
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
    foreach ($allFeatures as $f) {
        $item = $f['Features'];

        $company_code = $this->Session->read('company_code');
        // if($company_code == 'GAAR' && $item['feature_path'] == 'Attendance/showregister'){
        //     $item['feature_path'] = 'AttendanceOld/showregister';
        // }
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
