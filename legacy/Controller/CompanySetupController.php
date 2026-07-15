<?php

class CompanySetupController extends AppController
{
    public $name = 'CompanySetup';
    public $uses = array('CentralControl', 'PlanPaymentHistory', 'EmpDocument', 'EmpFam', 'Education', 'WorkExperience', 'EmployeeJoin', 'EmployeeSalaryStructure', 'EmployeeConfig', 'Family', 'passport', 'Promotion', 'NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmpAlterationDetails', 'ReportCriterias', 'SalaryIncrement', 'SalaryIncrementDetails', 'ComponentIncrement', 'EditPunches', 'Plan', 'Features', 'PlanFeature', 'CentralUserCredentials'); // Edited by Akshay on 17-4-2025
    //end
    public $components = array('MasterdataManagement');

    public function index()
    {

        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();

        $this->set('arr_branches', $arr_branches);
    }
    public function getCompanyFeatures()
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
        'Features.feature_key' => 'company',
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
        foreach ($allFeatures as $f) {
            $item = $f['Features'];
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

    public function createRazorpayOrder()
    {
        $key_id = "rzp_test_RUncVaytiPXcE3";
        $key_secret = "5mvotOLNsHZXDeb5g5YaiXRE";

        $amount = $_POST['amount'] * 100;
        $receipt = "order_rcpt_" . time();

        $data = [
            'amount' => $amount,
            'currency' => 'INR',
            'receipt' => $receipt,
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$key_id:$key_secret");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);

        echo json_encode([
            'key' => $key_id,
            'order_id' => $response['id'],
            'amount' => $response['amount']
        ]);
        exit;
    }
    public function verifyPayment()
    {
        $this->Plan->setDataSource('controldb');
        $this->CentralUserCredentials->setDataSource('controldb');

        $razorpay_payment_id = $_POST['razorpay_payment_id'];
        $razorpay_order_id = $_POST['razorpay_order_id'];
        $razorpay_signature = $_POST['razorpay_signature'];
        $plan_id = $_POST['plan_id'];

        $key_secret = "5mvotOLNsHZXDeb5g5YaiXRE";

        $generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, $key_secret);

        if ($generated_signature === $razorpay_signature) {
            $paymentDetails = $this->getRazorpayPaymentDetails($razorpay_payment_id);

            $this->loadModel('PlanPaymentHistory');

            $transaction_data = [
                'user_id' => $this->Session->read('login_user_id'),
                'change_plan_id' => $plan_id,
                'razorpay_payment_id' => $paymentDetails['id'],
                'razorpay_order_id' => $paymentDetails['order_id'],
                'amount' => $paymentDetails['amount'] / 100,
                'method' => $paymentDetails['method'],
                'email' => $paymentDetails['email'],
                'contact' => $paymentDetails['contact'],
                'description' => $paymentDetails['description'],
                'status' => $paymentDetails['status'],
                'created_by' => $this->Session->read('login_user_id'),
                'creation_date' => date('Y-m-d H:i:s'),
                'modified_by' => null,
                'modification_date' => null,
            ];

            if ($this->PlanPaymentHistory->save($transaction_data)) {

                if ($paymentDetails['status'] === 'captured') {
                    $user_id = $this->Session->read('login_user_id');

                    // Convert to lowercase for comparison
                    $lower_user_id = strtolower($user_id);
                    $plan_id_value = (int)$plan_id;

                    $this->CentralUserCredentials->query("
                            UPDATE user_credentials 
                            SET plan_id = {$plan_id_value}
                            WHERE LOWER(user_id) = LOWER('{$lower_user_id}')
                        ");

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Payment successful and plan upgraded successfully.'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'failed',
                        'message' => 'Payment not captured. Plan not updated.'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save payment history.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'invalid',
                'message' => 'Payment verification failed.'
            ]);
        }

        exit;
    }


    public function getRazorpayPaymentDetails($payment_id)
    {
        $key_id = "rzp_test_RUncVaytiPXcE3";
        $key_secret = "5mvotOLNsHZXDeb5g5YaiXRE";

        $ch = curl_init("https://api.razorpay.com/v1/payments/" . $payment_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$key_id:$key_secret");
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
