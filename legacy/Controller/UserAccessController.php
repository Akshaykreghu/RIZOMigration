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
class UseraccessController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Useraccess';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Useraccess', 'EmployeeMenu', 'Organization','EmployeeProfessionalDetails','CentralUserCredentials');
    public $components = array('MasterdataManagement');

    public function index()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        //edited by arul on 18/11/2019 Employee Id changed to emp company id
        //$arr_employee = $this->EmployeeDetails->find('all', array('conditions' => array('status' => 1)));

        // Edited by Akshay on 6-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND emp_details.branch_code = '$is_ho' ";
            }
        }
        
        $arr_employee1 = $this->EmployeeDetails->query("select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_proff.emp_fkey=emp_details.emp_pkey where emp_details.status=1 $branch_condition order by emp_details.first_name ASC ");
        // End
        $arr_employee = array();
        foreach ($arr_employee1 as $key => $value) {
            $arr_employee[$key]['EmployeeDetails']['emp_pkey'] = $value['emp_details']['emp_pkey'];
            $arr_employee[$key]['EmployeeDetails']['first_name'] = $value['emp_details']['first_name'];
            $arr_employee[$key]['EmployeeDetails']['last_name'] = $value['emp_details']['last_name'];
            $arr_employee[$key]['EmployeeDetails']['emp_company_id'] = $value['emp_proff']['emp_company_id'];
        }

        $this->set('arr_employee', $arr_employee);
        //end
        $arr_order = array('parent_id ASC');
        $arr_parent = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array('EmployeeMenu.parent_id = 0', 'EmployeeMenu.active' => 'Y')
        ));

        $arr_child = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array('EmployeeMenu.parent_id != 0', 'EmployeeMenu.active' => 'Y')
        ));
        //  debug($arr_menu);

        $this->set('arr_parent', $arr_parent);
        $this->set('arr_child', $arr_child);
        // back button
         $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
              $user_group = $this->Session->read('user_group');
      
        $this->set('planId', $planId);
        $this->set('user_group', $user_group);
    }

    public function insec($emp_pkey = 0) {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $arr_order = array('parent_id ASC');
        $arr_parent = $this->EmployeeMenu->query("select u.status,EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active
        from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey='$emp_pkey' and u.active='Y' where EmployeeMenu.parent_id = 0 and EmployeeMenu.active = 'Y' and EmployeeMenu.is_default != 'M'");
        $adminid = '0';
        $fetchUser = $this->Useraccess->query("select * from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$emp_pkey'");
        $this->set('fetchUser', $fetchUser);
        $arr_child = $this->EmployeeMenu->query("select EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey=$emp_pkey and u.active='Y' where  EmployeeMenu.parent_id != 0 and is_default !='M' and EmployeeMenu.active = 'Y'   "); //u.active = 'Y' and 
        $this->set('arr_parent', $arr_parent);
        $this->set('arr_child', $arr_child);
        $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and active = 'Y' ");
        $this->set('arr_useraccess', $arr_useraccess);
        $payroUser = $this->Useraccess->query("select payro_priv from emp_proff where emp_fkey ='$emp_pkey'");
        $this->set('payroUser', $payroUser);
    }

     public function indexnew()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        //edited by arul on 18/11/2019 Employee Id changed to emp company id
        //$arr_employee = $this->EmployeeDetails->find('all', array('conditions' => array('status' => 1)));

        // Edited by Akshay on 6-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND emp_details.branch_code = '$is_ho' ";
            }
        }

$arr_employee1 = $this->EmployeeDetails->query("select emp_details.emp_pkey, emp_details.first_name, emp_details.last_name, emp_proff.emp_company_id, user_credentials.avatar from emp_details join emp_proff on emp_proff.emp_fkey=emp_details.emp_pkey left join user_credentials on user_credentials.emp_fkey=emp_details.emp_pkey where emp_details.status=1 $branch_condition order by emp_details.first_name ASC ");
        // End
        $arr_employee = array();
        foreach ($arr_employee1 as $key => $value) {
            $arr_employee[$key]['EmployeeDetails']['emp_pkey'] = $value['emp_details']['emp_pkey'];
            $arr_employee[$key]['EmployeeDetails']['first_name'] = $value['emp_details']['first_name'];
            $arr_employee[$key]['EmployeeDetails']['last_name'] = $value['emp_details']['last_name'];
            $arr_employee[$key]['EmployeeDetails']['emp_company_id'] = $value['emp_proff']['emp_company_id'];
            $arr_employee[$key]['EmployeeDetails']['avatar'] = isset($value['user_credentials']['avatar']) ? $value['user_credentials']['avatar'] : '';
        }

        $this->set('arr_employee', $arr_employee);
        //end
        $arr_order = array('parent_id ASC');
        $arr_parent = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array('EmployeeMenu.parent_id = 0', 'EmployeeMenu.active' => 'Y')
        ));

        $arr_child = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array('EmployeeMenu.parent_id != 0', 'EmployeeMenu.active' => 'Y')
        ));
        //  debug($arr_menu);

        $this->set('arr_parent', $arr_parent);
        $this->set('arr_child', $arr_child);

        // back button
         $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
              $user_group = $this->Session->read('user_group');
      
        $this->set('planId', $planId);
        $this->set('user_group', $user_group);

        // Fetch Add-on Features from Central DB
        $this->CentralUserCredentials->setDataSource('controldb');
        $features = $this->CentralUserCredentials->query("SELECT * FROM features ORDER BY display_order ASC");
        $this->set('features', $features);

        // Fetch Branches from Tenant DB
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $branches = $this->EmployeeDetails->query("SELECT branch_code, branch_name FROM branches WHERE status = 1 AND deleted < 0 ORDER BY branch_name ASC");
        $this->set('branches', $branches);
    }

    public function saveFeatureAccess()
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        
        $data = $this->request->data;
        $user_fkey = $data['user_fkey'];
        $feature_id = $data['feature_id'];
        $active = $data['active'];
        $mode = isset($data['mode']) ? $data['mode'] : 'branch'; // 'branch' or 'hierarchy'

        // Check if is_hierarchy column exists, if not add it (Safeguard)
        try {
            $this->EmployeeDetails->query("ALTER TABLE user_feature_branch_access ADD COLUMN is_hierarchy char(1) DEFAULT 'N' AFTER branch_fkey");
        } catch (Exception $e) {
            // Column likely already exists
        }

        $existing = $this->Useraccess->find('first', array(
            'conditions' => array(
                'user_fkey' => $user_fkey,
                'menu_id' => $feature_id, // Map feature_id to menu_id for addon
                'organization_id' => '1'
            )
        ));

        $saveData = array();
        if ($existing) {
            $saveData['user_access_pkey'] = $existing['Useraccess']['user_access_pkey'];
        }
        $saveData['user_fkey'] = $user_fkey;
        $saveData['menu_id'] = 0;
        $saveData['active'] = $active;
        $saveData['organization_id'] = '1';
        $saveData['status'] = '1';

        if ($this->Useraccess->save($saveData)) {
            if ($active == 'Y') {
                if ($mode == 'hierarchy') {
                    // Deactivate all existing branch entries and add hierarchy entry
                    $this->EmployeeDetails->query("UPDATE user_feature_branch_access SET active = 'N' WHERE user_fkey = '$user_fkey' AND feature_fkey = '$feature_id'");
                    $this->EmployeeDetails->query("INSERT INTO user_feature_branch_access (user_fkey, feature_fkey, branch_fkey, is_hierarchy, active, created_at) VALUES ('$user_fkey', '$feature_id', 'n', 'Y', 'Y', NOW())");
                } else {
                    // Branch Wise: Default all branches to 'Y' and set is_hierarchy = 'N'
                    $this->EmployeeDetails->query("UPDATE user_feature_branch_access SET active = 'N' WHERE user_fkey = '$user_fkey' AND feature_fkey = '$feature_id'");
                    $branches = $this->EmployeeDetails->query("SELECT branch_code FROM branches WHERE status = 1 AND deleted < 0");
                    foreach ($branches as $b) {
                        $branch_code = $b['branches']['branch_code'];
                        $this->EmployeeDetails->query("INSERT INTO user_feature_branch_access (user_fkey, feature_fkey, branch_fkey, is_hierarchy, active, created_at) VALUES ('$user_fkey', '$feature_id', '$branch_code', 'N', 'Y', NOW())");
                    }
                }
            } else {
                // Feature is toggled OFF: Deactivate all branch access for this feature and user
                $this->EmployeeDetails->query("UPDATE user_feature_branch_access SET active = 'N' WHERE user_fkey = '$user_fkey' AND feature_fkey = '$feature_id'");
            }
            echo json_encode(array('status' => 'success', 'msg' => 'Menu access updated successfully'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'Failed to update feature access'));
        }
    }

   public function saveBranchAccess()
{
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    $data = $this->request->data;

    $user_fkey = isset($data['user_fkey']) ? $data['user_fkey'] : '';
    $feature_fkey = isset($data['feature_id']) ? $data['feature_id'] : '';
    $branches = isset($data['branches']) ? $data['branches'] : array(); // FIX HERE

    // Reset existing access
    $this->EmployeeDetails->query("
        UPDATE user_feature_branch_access 
        SET active = 'N' 
        WHERE user_fkey = '$user_fkey' 
        AND feature_fkey = '$feature_fkey'
    ");

    if (!empty($branches)) {
        foreach ($branches as $branch_code) {
            $this->EmployeeDetails->query("
                INSERT INTO user_feature_branch_access 
                (user_fkey, feature_fkey, branch_fkey, is_hierarchy, active, created_at) 
                VALUES 
                ('$user_fkey', '$feature_fkey', '$branch_code', 'N', 'Y', NOW())
            ");
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'msg' => 'No branch selected'
        ));
        return;
    }

    echo json_encode(array(
        'status' => 'success',
        'msg' => 'Branch access updated'
    ));
}

    public function getFeatureBranches($user_fkey = 0, $feature_id = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        
        $data = $this->EmployeeDetails->query("SELECT branch_fkey, is_hierarchy FROM user_feature_branch_access WHERE user_fkey = '$user_fkey' AND feature_fkey = '$feature_id' AND active = 'Y'");
        
        $branches = array();
        $is_hierarchy = 'N';
        foreach ($data as $b) {
            $branches[] = $b['user_feature_branch_access']['branch_fkey'];
            if ($b['user_feature_branch_access']['is_hierarchy'] == 'Y') {
                $is_hierarchy = 'Y';
            }
        }
        
        echo json_encode(array('branches' => $branches, 'is_hierarchy' => $is_hierarchy));
    }

    public function insecs($emp_pkey = 0)
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_order = array('parent_id ASC');

        // Only show items where is_default = 'Y' for the "Default System Menus" column
        $arr_parent = $this->EmployeeMenu->query("select u.status,EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active
        from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey='$emp_pkey' and u.active='Y' 
        where EmployeeMenu.parent_id = 0 and EmployeeMenu.active = 'Y' and EmployeeMenu.is_default = 'Y'");
        
        $adminid = '0';
        $fetchUser = $this->Useraccess->query("select * from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$emp_pkey'");
        $this->set('fetchUser', $fetchUser);
        
        $arr_child = $this->EmployeeMenu->query("select EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active 
        from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey=$emp_pkey and u.active='Y' 
        where  EmployeeMenu.parent_id != 0 and EmployeeMenu.active = 'Y' and EmployeeMenu.is_default = 'Y'");
        
        $this->set('arr_parent', $arr_parent);
        $this->set('arr_child', $arr_child);
        $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and active = 'Y' ");
        $this->set('arr_useraccess', $arr_useraccess);
        $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$emp_pkey'");
        $this->set('payroUser', $payroUser);

       $this->CentralUserCredentials->setDataSource('controldb');

$company_code = $this->Session->read('company_code');

$data = $this->CentralUserCredentials->find('first', array(
    'conditions' => array(
        'CentralUserCredentials.company_code' => $company_code
    ),
    'fields' => array('CentralUserCredentials.plan_id'),
    'recursive' => -1
));

$planId = !empty($data)
    ? (int)$data['CentralUserCredentials']['plan_id']
    : 0;   // safer default
    $planId = (int)$planId;
$company_code = addslashes($company_code);


$features = $this->CentralUserCredentials->query("
    SELECT features.*
    FROM features
    WHERE features.feature_id IN (
        SELECT feature_id FROM plan_features 
        WHERE plan_id = $planId AND is_enabled = 1
        
        UNION
        
        SELECT feature_id FROM company_addons 
        WHERE company_code = '$company_code'
        AND (expiry_date IS NULL OR expiry_date >= CURDATE())
    )
    ORDER BY features.display_order ASC
");
$this->set('features', $features);

// edited by bindu 01-04-2026
 $featureIds = array();
        foreach ($features as $f) {
            $featureIds[] = (int)$f['features']['feature_id'];
        }
        $addon_useraccess = array();
        if (!empty($featureIds)) {
            $featureIdList = implode(',', $featureIds);
            $this->Useraccess->useDbConfig = $this->Session->read('ds');
            $addon_useraccess = $this->Useraccess->query("
                SELECT * FROM user_access
                WHERE user_fkey = '$emp_pkey'
                  AND active    = 'Y'
                  AND menu_id   IN ($featureIdList)
                  AND menu_id   NOT IN (SELECT menu_id FROM emp_menu)
            ");
        }
        $this->set('addon_useraccess', $addon_useraccess);
        // edited by bindu 01-04-2026 end

        // Fetch Branches from Tenant DB (Needed for insec.ctp AJAX load)
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $branches = $this->EmployeeDetails->query("SELECT branch_code, branch_name FROM branches WHERE status = 1 AND deleted < 0 ORDER BY branch_name ASC");
        $this->set('branches', $branches);

        // Fetch user's current branch access for features
        $user_branch_access = $this->EmployeeDetails->query("SELECT feature_fkey, is_hierarchy FROM user_feature_branch_access WHERE user_fkey = '$emp_pkey' AND active = 'Y'");
        $feature_modes = array();
        foreach ($user_branch_access as $uba) {
            $f_id = $uba['user_feature_branch_access']['feature_fkey'];
            $feature_modes[$f_id] = $uba['user_feature_branch_access']['is_hierarchy'];
        }
        $this->set('feature_modes', $feature_modes);
    }
//  public function autoAllocateDefault($emp_fkey = 0)
//     {
//         $this->autoRender = false;
//         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//         $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

//         // Count ANY user_access record (active OR inactive) linked to a default menu.
//         // This ensures we never overwrite menus an admin has individually toggled off.
//         $existing = $this->EmployeeDetails->query("
//             SELECT COUNT(*) AS cnt
//             FROM user_access ua
//             JOIN emp_menu em ON em.menu_id = ua.menu_id
//             WHERE ua.user_fkey = '$emp_fkey'
//               AND em.is_default = 'Y'
//               AND em.active   = 'Y'
//         ");

//         $count = isset($existing[0][0]['cnt']) ? (int)$existing[0][0]['cnt'] : 0;

//         if ($count === 0) {
//             // First time ever — no records exist at all — auto-allocate now
//             $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')");
//             echo json_encode(array(
//                 'status'  => 'allocated',
//                 'message' => 'Default menus auto-allocated'
//             ));
//         } else {
//             // Records already exist (some may be toggled off by admin — respect that)
//             echo json_encode(array(
//                 'status'  => 'exists',
//                 'message' => 'Default menus already set up'
//             ));
//         }
//     }

public function autoAllocateDefault($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // Check if this employee has ANY rows in user_access at all
        $existing = $this->EmployeeDetails->query("
            SELECT COUNT(*) AS cnt
            FROM user_access
            WHERE user_fkey = '$emp_fkey'
        ");

        $count = isset($existing[0][0]['cnt']) ? (int)$existing[0][0]['cnt'] : 0;

        if ($count === 0) {
            // First time — no records exist — insert all default menus
            $defaultMenus = $this->EmployeeDetails->query("
                SELECT menu_id
                FROM emp_menu
                WHERE is_default = 'Y'
                  AND active = 'Y'
            ");

            $inserted = 0;
            foreach ($defaultMenus as $row) {
                $menuId = (int)$row['emp_menu']['menu_id'];
                $this->EmployeeDetails->query("
                    INSERT INTO user_access (organization_id, user_fkey, menu_id, active, status)
                    VALUES ('1', '$emp_fkey', '$menuId', 'Y', 1)
                ");
                $inserted++;
            }

            echo json_encode(array(
                'status'  => 'allocated',
                'message' => $inserted . ' default menu(s) auto-allocated'
            ));
        } else {
            // Already has records — do nothing
            echo json_encode(array(
                'status'  => 'exists',
                'message' => 'Default menus already set up'
            ));
        }
    }
    public function save($emp_pkey = '', $s = '')
    {
        $this->autoRender = false;
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        if ($s == 'All') {
            $where = '';
            $s = 0;
            $wh = '';
        } else {
            $where = "and parent_id = $s or menu_id = '$s' ";
            $wh = "";
        }
        $par = $this->EmployeeMenu->query("select menu_id from emp_menu where parent_id = '$s' $wh ");

        if (count($par) > 0) {
            $ch = $this->EmployeeMenu->query("select menu_id from emp_menu where active = 'Y' $where ORDER BY parent_id ASC ");

            foreach ($ch as $menu) {
                $this->Useraccess->useDbConfig = $this->Session->read('ds');
                $arr_form_data = array();
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = '';
                $arr_form_data['menu_id'] = '';
                $arr_form_data['active'] = '';
                $arr_form_data['status'] = '1';
                $id = $menu['emp_menu']['menu_id'];
                $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $id ");
                if (count($arr_useraccess) > 0) {

                    $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                }
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $id;
                $arr_form_data['active'] = 'Y';

                $this->Useraccess->saveAll($arr_form_data);
            }
        } else {
            // SINGLE MENU SAVE
            $arr_form_data = array();
            
            // Check if this menu has a parent. If so, auto-enable the parent too.
            $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
            $menu_info = $this->EmployeeMenu->query("SELECT parent_id FROM emp_menu WHERE menu_id = '$s'");
            if (!empty($menu_info) && $menu_info[0]['emp_menu']['parent_id'] != 0) {
                $parent_id = $menu_info[0]['emp_menu']['parent_id'];
                
                // Auto-enable Parent
                $parent_ua = $this->Useraccess->query("SELECT * FROM user_access WHERE user_fkey = $emp_pkey AND menu_id = $parent_id");
                $parent_data = array(
                    'organization_id' => '1',
                    'user_fkey'       => $emp_pkey,
                    'menu_id'         => $parent_id,
                    'active'          => 'Y',
                    'status'          => '1'
                );
                if (!empty($parent_ua)) {
                    $parent_data['user_access_pkey'] = $parent_ua[0]['user_access']['user_access_pkey'];
                }
                $this->Useraccess->create();
                $this->Useraccess->save($parent_data);
            }

            $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $s ");

            if (count($arr_useraccess) > 0) {
                $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
            }
            $arr_form_data['organization_id'] = '1';
            $arr_form_data['user_fkey'] = $emp_pkey;
            $arr_form_data['menu_id'] = $s;
            $arr_form_data['active'] = 'Y';
            $arr_form_data['status'] = '1'; 
            try {
                $this->Useraccess->create();
                $this->Useraccess->save($arr_form_data);
            } catch (Exception $ex) {
            }
        }
        echo json_encode(array('msg' => 'Useraccess saved successfully'));

    }
    // edited by bindu 01-04-2026 end
    public function addDefault($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        }else{
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Menu Not Added'
        ));
        }
    }

    public function resetDefault($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ");
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        }else{
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Menu Not Added'
        ));
        }
    }

    public function deletemens($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        }else{
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Menu Not Added'
        ));
        }
    }

    public function listuseraccess() {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;

        $offset = ($page - 1) * $limit;

        $sortcolumn = isset($arr_data['sort']) ? $arr_data['sort'] : '';

        $sortorder = isset($arr_data['order']) ? $arr_data['order'] : '';

        $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '9';

        $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $user_fkey and active = 'Y' ");

        //debug($arr_useraccess);
        $arr_active_menus = array();
        foreach ($arr_useraccess as $key => $val) {
            $arr_active_menus[] = $val['user_access']['menu_id'];
        }

        if ($sortcolumn != '' && $sortorder !== '') {
            $arr_order = array($sortcolumn . ' ' . $sortorder);
        } else {
            $arr_order = array('parent_id ASC');
        }
        $totalcount = $this->EmployeeMenu->find("count", array(
            'conditions' => array('EmployeeMenu.parent_id != 0')
        ));

        $arr_menu = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array(/* 'EmployeeMenu.parent_id != 0', */'EmployeeMenu.active' => 'Y'),
            'offset' => $offset,
            'limit' => $limit
        ));
        //debug($arr_menu);
        $rows = array();
        //debug($arr_menu);
        foreach ($arr_menu as $key => $val) {
            $val['EmployeeMenu']['parent'] = $val[0]['parent'];
            if (in_array($val['EmployeeMenu']['menu_id'], $arr_active_menus)) {
                $val['EmployeeMenu']['accessallow'] = 'Y';
            } else {
                $val['EmployeeMenu']['accessallow'] = 'N';
            }
            $rows[] = $val['EmployeeMenu'];
        }

        $resp_data["total"] = $totalcount;
        $resp_data["rows"] = $rows;




        echo json_encode($resp_data);
    }

//     public function delete($emp_pkey = '', $s = '') {
//         //   debug($emp_pkey);
//         //    debug($s);
//         $this->autoRender = false;
//         if ($s == 'All') {
//             $where = '';
//             $wh = '';
//         } else {
//             $where = "and parent_id = $s or menu_id = '$s' ";
//             $wh = "and menu_id = $s";
//         }
//         //    $this -> Organization -> useDbConfig = $this -> Session -> read('ds');
//         $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
//         $this->Useraccess->useDbConfig = $this->Session->read('ds');

//         $par = $this->EmployeeMenu->query("select menu_id from emp_menu where parent_id = '0' $wh ");

//         if (count($par) > 0) {
//             $ch = $this->EmployeeMenu->query("select menu_id from emp_menu where active = 'Y'  $where ");
//             //  debug($ch);
//             foreach ($ch as $menu) {
//                 $this->Useraccess->useDbConfig = $this->Session->read('ds');
//                 $arr_form_data = array();
//                 $arr_form_data['organization_id'] = '1';
//                 $arr_form_data['user_fkey'] = '';
//                 $arr_form_data['menu_id'] = '';
//                 $arr_form_data['active'] = '';
//                 //    debug($menu);
//                 $id = $menu['emp_menu']['menu_id'];
//                 //   debug($id);
//                 $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $id ");
//                 //  debug($arr_useraccess);
//                 if (count($arr_useraccess) > 0) {

//                     $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
//                 }
//                 $arr_form_data['organization_id'] = '1';
//                 $arr_form_data['user_fkey'] = $emp_pkey;
//                 $arr_form_data['menu_id'] = $id;
//                 $arr_form_data['active'] = 'N';
//                 $arr_form_data['status'] = '1';
//                 $this->Useraccess->saveAll($arr_form_data);
//             }
//         } else {
//             $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $s ");

//             //  debug($arr_useraccess);
//             $array_men = explode(',', $s);
//             //    debug($array_men);

//             if (count($arr_useraccess) > 0) {


//                 $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
//                 $arr_form_data['organization_id'] = '1';
//                 $arr_form_data['user_fkey'] = $emp_pkey;
//                 $arr_form_data['menu_id'] = $s;
//                 $arr_form_data['active'] = 'N';
//                 $this->Useraccess->save($arr_form_data);
//             }
//         } echo json_encode(array('msg' => 'Useraccess saved successfully'));
//     }

//     public function Employee($user_pkey = 0, $DD = '') {
//         $this->autoRender = false;
//         //  debug($DD);
//         $this->Useraccess->useDbConfig = $this->Session->read('ds');
//         // $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
//         // debug($user_pkey);
//         // debug($arr_useraccess);
//         $adminid = '#';
//         //  $arr_empdashboard = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='EmpDashboard'");
// // debug($adminid);
//         //  debug($empid);
//         $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

//         $pkey = $fetchUser['0']['Useraccess']['user_access_pkey'];
// //  debug($pkey);
//         if ($pkey != '') {
//             $arr_useraccess_data['user_access_pkey'] = $pkey;
//         }
//         $arr_useraccess_data['organization_id'] = '1';
//         $arr_useraccess_data['user_fkey'] = $user_pkey;
//         $arr_useraccess_data['menu_id'] = $adminid;
//         if ($DD == "EMPLOYEE") {
//             $arr_useraccess_data['active'] = 'Y';
//         } else {
//             $arr_useraccess_data['active'] = 'N';
//         }
//         $arr_useraccess_data['status'] = '1';


//         $this->Useraccess->save($arr_useraccess_data);
//     }

    public function delete($emp_pkey = '', $s = '')
    {
        $this->autoRender = false;
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        if ($s == 'All') {
            $where = '';
            $wh = '';
        } else {
            $where = "and parent_id = $s or menu_id = '$s' ";
            $wh = "and menu_id = $s";
        }

        $par = $this->EmployeeMenu->query("select menu_id from emp_menu where parent_id = '0' $wh ");


        if (count($par) > 0) {
            $ch = $this->EmployeeMenu->query("select menu_id from emp_menu where active = 'Y' $where ");
            foreach ($ch as $menu) {
                $this->Useraccess->useDbConfig = $this->Session->read('ds');
                $arr_form_data = array();
                $id = $menu['emp_menu']['menu_id'];
                $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $id ");
                if (count($arr_useraccess) > 0) {
                    $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                }
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $id;
                $arr_form_data['active'] = 'N';
                $arr_form_data['status'] = '1';
                $this->Useraccess->create();
                $this->Useraccess->save($arr_form_data);
            }
        } else {
            // SINGLE MENU DELETE
            $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $s ");

            if (count($arr_useraccess) > 0) {
                $ua_data = array(
                    'user_access_pkey' => $arr_useraccess['0']['user_access']['user_access_pkey'],
                    'organization_id'  => '1',
                    'user_fkey'        => $emp_pkey,
                    'menu_id'          => $s,
                    'active'           => 'N',
                    'status'           => '1'
                );
                $this->Useraccess->create();
                $this->Useraccess->save($ua_data);

                // If this was a submenu, check if any other siblings are still active.
                // If NO siblings are active, disable the parent too.
                $menu_info = $this->EmployeeMenu->query("SELECT parent_id FROM emp_menu WHERE menu_id = '$s'");
                if (!empty($menu_info) && $menu_info[0]['emp_menu']['parent_id'] != 0) {
                    $parent_id = $menu_info[0]['emp_menu']['parent_id'];
                    
                    // Count REMAINING active submenus for this parent
                    $active_siblings = $this->Useraccess->query("
                        SELECT COUNT(*) AS cnt 
                        FROM user_access ua
                        WHERE ua.user_fkey = $emp_pkey 
                          AND ua.active = 'Y'
                          AND ua.menu_id IN (SELECT menu_id FROM emp_menu WHERE parent_id = $parent_id)
                    ");
                    $count = isset($active_siblings[0][0]['cnt']) ? (int)$active_siblings[0][0]['cnt'] : 0;

                    
                    if ($count === 0) {
                        // All submenus are OFF -> Disable Parent
                        $parent_ua = $this->Useraccess->query("SELECT user_access_pkey FROM user_access WHERE user_fkey = $emp_pkey AND menu_id = $parent_id");
                        if (!empty($parent_ua)) {
                            $parent_off = array(
                                'user_access_pkey' => $parent_ua[0]['user_access']['user_access_pkey'],
                                'organization_id'  => '1',
                                'user_fkey'        => $emp_pkey,
                                'menu_id'          => $parent_id,
                                'active'           => 'N',
                                'status'           => '1'
                            );
                            $this->Useraccess->create();
                            $this->Useraccess->save($parent_off);
                        }
                    }
                }
            }
        }
        echo json_encode(array('msg' => 'Useraccess updated successfully'));
    }

       public function Employee($user_pkey = 0, $DD = '')
    {
        $this->autoRender = false;
        //  debug($DD);
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        // $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = '#';
        //  $arr_empdashboard = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='EmpDashboard'");
        // debug($adminid);
        //  debug($empid);
        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = $fetchUser['0']['Useraccess']['user_access_pkey'];
        //  debug($pkey);
        if ($pkey != '') {
            $arr_useraccess_data['user_access_pkey'] = $pkey;
        }
        $arr_useraccess_data['organization_id'] = '1';
        $arr_useraccess_data['user_fkey'] = $user_pkey;
        $arr_useraccess_data['menu_id'] = $adminid;
        if ($DD == "EMPLOYEE") {
            $arr_useraccess_data['active'] = 'Y';
        } else {
            $arr_useraccess_data['active'] = 'N';
        }
        $arr_useraccess_data['status'] = '1';


        $this->Useraccess->save($arr_useraccess_data);
    }
    public function admin($user_pkey = 0, $DD = '') {
        $this->autoRender = false;
        //  debug($DD);
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        // $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = '0';
        //  $arr_empdashboard = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='EmpDashboard'");
// debug($adminid);
        //  debug($empid);
        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = isset($fetchUser['0']['Useraccess']['user_access_pkey']) ? $fetchUser['0']['Useraccess']['user_access_pkey'] : '';
//  debug($pkey);
        if ($pkey != '') {
            $arr_useraccess_data['user_access_pkey'] = $pkey;
        }
        $arr_useraccess_data['organization_id'] = '1';
        $arr_useraccess_data['user_fkey'] = $user_pkey;
        $arr_useraccess_data['menu_id'] = $adminid;
        if ($DD == "ADMINS") {
            $arr_useraccess_data['active'] = 'Y';
        } else {
            $arr_useraccess_data['active'] = 'N';
        }
        $arr_useraccess_data['status'] = '1';


        $this->Useraccess->save($arr_useraccess_data);
    }
    public function admin2($user_pkey = 0, $DD = '')
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($DD == id) {
            $User =  $this->EmployeeProfessionalDetails->query("UPDATE emp_proff SET payro_priv = '0' WHERE emp_fkey ='$user_pkey'");
        } elseif ($DD == ADMINSS) {
            $User =  $this->EmployeeProfessionalDetails->query("UPDATE emp_proff SET payro_priv = '1' WHERE emp_fkey ='$user_pkey'");
        }
        $fetchUser = $this->Useraccess->query("select payro_priv from emp_proff as Useraccess where emp_fkey ='$user_pkey'");
        $payro_priv = $fetchUser['0']['Useraccess']['payro_priv'];
        echo json_encode(array(
            'status' => 'success',
            'message' => $payro_priv
        ));
    }

    public function get() {
        $this->autoRender = FALSE;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        //      debug($_POST['emp_id']);
        $empids = $_POST['emp_id'];
        $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = $arr_useraccess['0']['empmenu']['menu_id'];
        $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$empids' and menu_id = '$adminid' and active = 'Y' ");
        //  debug($useracess);
        $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
        if ($access == 'Y') {
            $active = 'Y';
        } else {
            $active = 'N';
        }
// debug($active);
        echo json_encode($active);
    }

    public function deleteuser($user_access_pkey = 0) {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        if ($user_access_pkey != 0) {
            $this->Useraccess->updateAll(array('status' => 0), array('user_access_pkey' => $user_access_pkey));
            echo json_encode(array('msg' => 'User deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'User deletion failed!'));
        }
    }

    public function saveuseraccess() {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $menu_id = isset($arr_data['menu_id']) ? $arr_data['menu_id'] : '';
        $user_pkey = isset($arr_data['user_pkey']) ? $arr_data['user_pkey'] : '';
        $active = isset($arr_data['active']) ? $arr_data['active'] : '';
        //  debug($arr_data);
        $arr_useraccess = $this->Useraccess->find('all', array(
            'fields' => 'user_access_pkey',
            'conditions' => array(
                'menu_id' => $menu_id,
                'user_fkey' => $user_pkey
            )
                )
        );

        if (isset($arr_useraccess[0]['Useraccess']['user_access_pkey'])) {
            //Update
            $user_access_pkey = $arr_useraccess[0]['Useraccess']['user_access_pkey'];
            $this->Useraccess->updateAll(array('active' => "'$active'"), array('user_access_pkey' => $user_access_pkey));
            echo json_encode(array('msg' => 'User access updated successfully!'));
        } else {
            //Add
            $arr_useraccess_data = array();
            $arr_useraccess_data['organization_id'] = 1;
            $arr_useraccess_data['user_fkey'] = $user_pkey;
            $arr_useraccess_data['menu_id'] = $menu_id;
            $arr_useraccess_data['active'] = $active;
            $this->Useraccess->save($arr_useraccess_data);

            echo json_encode(array('msg' => 'User access saved successfully!'));
        }
    }

}
