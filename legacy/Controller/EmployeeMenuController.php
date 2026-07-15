<?php

/**  
 * Employee Menu Controller
 * Handles fetching of default menus and addon features for the employee workspace.
 */
App::uses('AppController', 'Controller');
App::uses('ConnectionManager', 'Model');

class EmployeeMenuController extends AppController {

    public $name = 'EmployeeMenu';
    
    /**
     * Models used by this controller.
     */
    public $uses = array(
        'Menu', 'EmployeeMenu', 'Useraccess', 'EmployeeDetails', 
        'Features', 'Plan', 'PlanFeature', 'CentralUserCredentials',
        'EmployeeProfessionalDetails'
    );

    public function index() {
        $this->set('emp_pkey', $this->Session->read('emp_fkey'));
    }

    public function addon() {
        $this->set('emp_pkey', $this->Session->read('emp_fkey'));
        // Renders addon.ctp
    }

    /**
     * Fetches default system menus allocated to the employee.
     * Logic: Joins emp_menu with user_access where active = 'Y'
     */
  public function getDefaultMenus() {

    $this->autoRender = false;

    $emp_pkey = $this->Session->read('emp_fkey');
    $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

    $menus = $this->EmployeeMenu->find('all', [
        'joins' => [
            [
                'table' => 'user_access',
                'alias' => 'UserAccess',
                'type' => 'INNER',
                'conditions' => [
                    'UserAccess.menu_id = EmployeeMenu.menu_id',
                    'UserAccess.user_fkey' => $emp_pkey,
                    'UserAccess.active' => 'Y'
                ]
            ]
        ],
        'fields' => [
            'EmployeeMenu.menu_id',
            'EmployeeMenu.parent_id',
            'EmployeeMenu.menu_url',
            'EmployeeMenu.menu_title',
            'EmployeeMenu.menu_name',
             'EmployeeMenu.iconCls' 
        ],
        'conditions' => [
            'EmployeeMenu.parent_id !=' => 0, 
            'EmployeeMenu.active' => 'Y',
            'EmployeeMenu.is_default' => 'Y'
        ],
        'order' => ['EmployeeMenu.menu_name ASC']
    ]);

    $result = [];

    foreach ($menus as $m) {
        $result[] = $m['EmployeeMenu'];
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}


    // public function getDefaultMenus() {
    //     $this->autoRender = false;
    //     $emp_fkey = $this->Session->read('emp_fkey');
        
    //     $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
    //     $this->Useraccess->useDbConfig = $this->Session->read('ds');

    //     // Robust query to fetch all child menus mapped in user_access
    //     $menus = $this->EmployeeMenu->query("
    //         SELECT DISTINCT em.* 
    //         FROM emp_menu AS em
    //         JOIN user_access AS ua ON ua.menu_id = em.menu_id
    //         WHERE ua.user_fkey = '$emp_fkey' 
    //           AND LCASE(ua.active) = 'y'
    //           AND LCASE(em.active) = 'y' 
    //           AND em.parent_id != 0
    //         ORDER BY em.menu_name ASC
    //     ");
        
    //     $result = [];
    //     foreach($menus as $m) {
    //         $result[] = $m['em'];
    //     }
        
    //     header('Content-Type: application/json');
    //     echo json_encode($result);
    //     exit;
    // }
// public function getDefaultMenus() {
//     $this->autoRender = false;

//     $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

//     $menus = $this->EmployeeMenu->query("
//         SELECT * 
//         FROM emp_menu
//         WHERE LCASE(active) = 'y'
//           AND parent_id != 0
//         ORDER BY menu_name ASC
//     ");

//     $result = [];
//     foreach ($menus as $m) {
//         $result[] = $m['emp_menu'];
//     }

//     header('Content-Type: application/json');
//     echo json_encode($result);
//     exit;
// }

    /**
     * Fetches specifically mapped addons/features from user_feature_branch_access.
     */
//     public function getAddonMenus() {
//         $this->autoRender = false;
//         $emp_fkey = $this->Session->read('emp_fkey');
        
//         $ds = $this->Session->read('ds');
//         $this->EmployeeDetails->useDbConfig = $ds;

//         /**
//          * Step 1: Get allocated feature IDs from Tenant Database.
//          * We use EmployeeDetails model to run the query on the tenant connection.
//          */
//         $allocatedResults = $this->EmployeeDetails->query("
//             SELECT DISTINCT feature_fkey 
//             FROM user_feature_branch_access 
//             WHERE user_fkey = '$emp_fkey' 
//               AND LCASE(active) = 'y'
//         ");
        
//         $featureIds = array();
//         foreach($allocatedResults as $r) {
//             if (isset($r['user_feature_branch_access']['feature_fkey'])) {
//                 $featureIds[] = $r['user_feature_branch_access']['feature_fkey'];
//             }
//         }

//         if (empty($featureIds)) {
//             echo json_encode([]);
//             exit;
//         }

//         /**
//          * Step 2: Fetch feature details from Central Database (controldb).
//          */
//         $this->Features->setDataSource('controldb');
//         $idsString = implode(',', array_map('intval', $featureIds));
        
//         $features = $this->Features->query("
//             SELECT * FROM features WHERE feature_id IN ($idsString) ORDER BY display_order ASC
//         ");

//         $grouped = [];

// foreach ($features as $f) {

//     $feature = isset($f['features']) ? $f['features'] : $f['Features'];
//     $key = !empty($feature['feature_key']) ? $feature['feature_key'] : 'Others';

//     if (!isset($grouped[$key])) {
//         $grouped[$key] = [];
//     }

//     $grouped[$key][] = $feature;
// }

// echo json_encode($grouped);
// exit;

     
//     }
public function getAddonMenus() {

    $this->autoRender = false;

    $emp_fkey = $this->Session->read('emp_fkey');
    $company_code = $this->Session->read('company_code');

    $ds = $this->Session->read('ds');
    $this->EmployeeDetails->useDbConfig = $ds;

    /**
     * Step 1: Get allocated feature IDs from Tenant DB
     */
    $allocatedResults = $this->EmployeeDetails->query("
        SELECT DISTINCT feature_fkey 
        FROM user_feature_branch_access 
        WHERE user_fkey = '$emp_fkey' 
          AND LCASE(active) = 'y'
    ");

    $featureIds = [];

    foreach ($allocatedResults as $row) {

        $r = isset($row['user_feature_branch_access']) 
            ? $row['user_feature_branch_access'] 
            : (isset($row[0]) ? $row[0] : $row);

        if (!empty($r['feature_fkey'])) {
            $featureIds[] = (int)$r['feature_fkey'];
        }
    }

    if (empty($featureIds)) {
        echo json_encode([]);
        exit;
    }

    /**
     * Step 2: Fetch feature details from Central DB
     */
    $this->Features->setDataSource('controldb');

    $idsString = implode(',', $featureIds);

    $features = $this->Features->query("
        SELECT * 
        FROM features 
        WHERE feature_id IN ($idsString) 
        ORDER BY display_order ASC
    ");

    $grouped = [];

    foreach ($features as $f) {

        $feature = isset($f['features']) 
            ? $f['features'] 
            : (isset($f['Features']) ? $f['Features'] : $f);

        /**
         * ✅ Company-based condition added here
         */
       $not_allowed_companies = [
    'KWMT','ABSG','MBCT','DRRC','SRTS','MRBS','DJIC','STCL',
    'SHYD','AGNG','ESNP','GTRA','VGNN','AYRK','VGFS','VSFS'
];

if (
    isset($feature['feature_key']) &&
    $feature['feature_key'] == 81 &&
    !in_array(strtoupper($company_code), $not_allowed_companies)
) {
    $feature['feature_path'] = 'AttendanceRegisterNew/indexneww';
}

        $key = !empty($feature['feature_key']) 
            ? $feature['feature_key'] 
            : 'Others';

        if (!isset($grouped[$key])) {
            $grouped[$key] = [];
        }

        $grouped[$key][] = $feature;
    }

    header('Content-Type: application/json');
    echo json_encode($grouped);
    exit;
}
     public function setFeatureSession() {
        $this->autoRender = false;
        
        // Handle both GET and POST for maximum flexibility
        $feature_id = isset($this->request->data['feature_id']) 
            ? $this->request->data['feature_id'] 
            : (isset($this->request->query['feature_id']) ? $this->request->query['feature_id'] : null);

        if ($feature_id) {
            $this->Session->write('current_feature_id', $feature_id);
            echo json_encode(['success' => true, 'feature_id' => $feature_id]);
        } else {
            // The user explicitly requested clearing the session feature state
            $this->Session->delete('current_feature_id');
            echo json_encode(['success' => true, 'message' => 'Feature ID cleared']);
        }
        exit;
    }
}