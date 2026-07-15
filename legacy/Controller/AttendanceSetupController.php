<?php

class AttendanceSetupController extends AppController
{
    public $name = 'AttendanceSetup';
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

   $allFeatures = $this->Features->find('all', [
    'conditions' => [
        'Features.feature_key' => 'Attendance',
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
    $company_code = strtoupper($this->Session->read('company_code'));

$not_allowed_companies = array(
    'KWMT','ABSG','MBCT','DRRC','SRTS','MRBS','DJIC','STCL',
    'SHYD','AGNG','ESNP','GTRA','VGNN','AYRK','VGFS','VSFS'
);
    // foreach ($allFeatures as $f) {
    //     $item = $f['Features'];
    //     $item['is_enabled'] = in_array($item['feature_id'], $enabled) ? 1 : 0;
    //     $result[] = $item;
    // }
    foreach ($allFeatures as $f) {

    $item = $f['Features'];

    // Change Regularisation path based on company
    if ($item['feature_path'] == 'Regularisation/adminindexnew') {
        if (in_array($company_code, $not_allowed_companies)) {
            $item['feature_path'] = 'Regularisation/adminindex';
        }
    }

     // Change Attendance Register path based on company
    if ($item['feature_path'] == 'AttendanceRegisterNew/indexneww') {
        if (in_array($company_code, $not_allowed_companies)) {
            $item['feature_path'] = 'AttendanceRegisterNew/index';
        }
    }
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
