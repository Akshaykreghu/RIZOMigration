<?php

class EmployeeJoinController extends AppController
{
    public $name = 'EmployeeJoin';
    public $uses = array('CentralControl', 'EmpDocument', 'EmpFam', 'Education', 'WorkExperience', 'EmployeeJoin', 'EmployeeSalaryStructure', 'EmployeeConfig', 'Family', 'passport', 'Promotion', 'NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmpAlterationDetails', 'ReportCriterias', 'SalaryIncrement', 'SalaryIncrementDetails', 'ComponentIncrement', 'EditPunches'); // Edited by Akshay on 17-4-2025
    //end
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */
    public function index()
    {
        $this->autoRender = FALSE;
        $user_group = $this->Session->read("user_group");
        $this->set("user", strtoupper($this->Session->read('company_code')));
        //edited by athira on 04-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $plan = $plan['0']['comp_contact_info']['plan'];
        $this->set('plan', $plan);
        //end
        $emp_pkeys = 0;
        $this->set('emp_pkeys', $emp_pkeys);
        $totalEmployees = $this->EmployeeDetails->query("
                     SELECT COUNT(*) as total 
                FROM emp_details WHERE emp_details.status!=0
and  emp_details.status!=4
            ");
        $totalEmployeeCount = $totalEmployees[0][0]['total'];
        $activeEmployees = $this->EmployeeDetails->query("
                SELECT COUNT(*) as total 
                FROM emp_details WHERE emp_details.status=1
            ");
        $activeEmployeeCount = $activeEmployees[0][0]['total'];
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        // $arr_active = $this->EmployeeDetails->query("select count(*) as presentcount from present_today");
        $arr_missingStructure = $this->EmployeeProfessionalDetails->query("
    SELECT COUNT(DISTINCT ep.emp_fkey) AS missingcount
    FROM emp_proff ep
    INNER JOIN emp_details ed ON ed.emp_pkey = ep.emp_fkey
    WHERE ep.structure_id IS NULL
      AND ed.status = 1
");

        $missingcount = $arr_missingStructure[0][0]['missingcount'];
        $active = isset($arr_active[0][0]['presentcount']) ? $arr_active[0][0]['presentcount'] : 0;
        $joinedThisMonth = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS total
FROM emp_proff ep
INNER JOIN emp_details ed ON ed.emp_pkey = ep.emp_fkey
WHERE YEAR(ep.joining_date) = YEAR(CURDATE())
  AND MONTH(ep.joining_date) = MONTH(CURDATE())
ORDER BY ep.joining_date ASC;
");

        $joined_count = $joinedThisMonth[0][0]['total'];

        $noticeEmployees = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS total
    FROM termination t
    INNER JOIN emp_proff e ON t.emp_fkey = e.emp_fkey
    WHERE t.status = 1
      AND CURDATE() <= DATE_ADD(t.submitted_date, INTERVAL e.notice_days DAY)
");
        // debug($noticeEmployees);
        $notice_count = $noticeEmployees[0][0]['total'];
        // debug($notice_count);
        $this->set('totalEmployeeCount', $totalEmployeeCount);
        $this->set('activeEmployeeCount', $activeEmployeeCount);
        $this->set('missing_salary', $missingcount);
        $this->set('joinedThisMonth', $joined_count);
        $this->set('notice_count', $notice_count);
        $company_code = $this->Session->read('company_code');
      
            $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
            $missed_prof = $this->checkProff();
            $this->set('active_emp_count', $active_emp_count);


            //Fetch Units for the company
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
            $this->set('arr_branches', $arr_branches);

            // $arr_Emp=$this->MasterdataManagement->getEmployeeListForCombo();
            // $this->set('arr_Emp',$arr_Emp);

            $arr_Des = $this->MasterdataManagement->getDesignationsListForCombo();
            // debug($arr_Des);
            $this->set('missed_prof', $missed_prof);
            $this->set('arr_Des', $arr_Des);

            // Fetching the maximum employee count from the comp_contact_info table
            //edited by athira on 28-12-2024
            $emp_max_count = $this->EmployeeDetails->query("SELECT max_emp_count FROM comp_contact_info");
            $emp_max_count = $emp_max_count[0]['comp_contact_info']['max_emp_count']; // Correcting the result structure
            // Passing 'emp_max_count' to the view
            $this->set('emp_max_count', $emp_max_count);
            //end
            $this->render('index');
        
    }

    public function downloadMissingSalary()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        // Fetch employees with no salary structure
        $employees = $this->EmployeeProfessionalDetails->query("
        SELECT 
    ep.emp_company_id,
    CONCAT_WS(' ', ed.first_name, ed.last_name) AS name,
    b.branch_name AS branch,
    ep.designation
FROM emp_proff ep
INNER JOIN emp_details ed 
    ON ed.emp_pkey = ep.emp_fkey
LEFT JOIN branches b 
    ON b.branch_code = ep.emp_branch
WHERE ep.structure_id IS NULL
  AND ed.status = 1
ORDER BY name ASC;
    ");

        // Initialize PDF
        require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

        Configure::write('debug', 0);
        $this->autoRender = false;
        if (!defined('COMPANY_NAME')) define('COMPANY_NAME', 'Employees with No Salary Structure');
        if (!defined('COMPANY_URLS')) define('COMPANY_URLS', ' ');

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(COMPANY_NAME);
        $pdf->SetTitle('Employees with No Salary Structure');

        // Set custom header with centered company name
        $pdf->setHeaderData('', 0, '', ''); // Clear default header
        $pdf->setPrintHeader(false); // Disable default header

        // Add a custom header manually
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 14);

        // Get page width
        $pageWidth = $pdf->getPageWidth();
        $margins = $pdf->getMargins();
        $usableWidth = $pageWidth - $margins['left'] - $margins['right'];

        $pdf->Cell($usableWidth, 5, COMPANY_NAME, 0, 1, 'C', 0, '', 0);
        $pdf->Ln(3);

        // Now set font for rest of the content
        $pdf->SetFont('helvetica', '', 11);

        // ✅ Heading with underline
        // $html = '';
        // $html .= '<h3 style="text-align:center; text-decoration:underline;">Employees with No Salary Structure</h3>';
        $html .= '<table border="1" cellpadding="5">
        <tr style="background-color:#f2f2f2;text-align:center;font-weight:bold;">
            <th>SI No</th>
            <th>Company ID</th>
            <th>Name</th>
            <th>Branch</th>
            <th>Designation</th>
        </tr>';

        // Table data
        $si = 1;
        foreach ($employees as $emp) {
            $html .= '<tr style="text-align:center;font: size 14px;">
            <td>' . $si++ . '</td>
            <td>' . (isset($emp['ep']['emp_company_id']) ? $emp['ep']['emp_company_id'] : '') . '</td>
            <td>' . (isset($emp[0]['name']) ? $emp[0]['name'] : '') . '</td>
            <td>' . (isset($emp['b']['branch']) ? $emp['b']['branch'] : '') . '</td>
            <td>' . (isset($emp['ep']['designation']) ? $emp['ep']['designation'] : '') . '</td>
        </tr>';
        }
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('NoSalaryStructureEmployees.pdf', 'D');
    }

    public function thisMonthJoining()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        // Fetch employees with no salary structure
        $employees = $this->EmployeeProfessionalDetails->query("
           SELECT
    CONCAT_WS(' ', ed.first_name, ed.last_name) AS name,
    ep.joining_date,
    b.branch_name AS branch,
    ep.designation
FROM emp_proff ep
INNER JOIN emp_details ed 
    ON ed.emp_pkey = ep.emp_fkey
LEFT JOIN branches b 
    ON b.branch_code = ep.emp_branch   -- adjust if your column is branch_id
WHERE YEAR(ep.joining_date) = YEAR(CURDATE())
  AND MONTH(ep.joining_date) = MONTH(CURDATE())
ORDER BY ep.joining_date ASC;
        ");

        // Initialize PDF
        require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

        // Disable CakePHP debug output
        Configure::write('debug', 0);
        $this->autoRender = false;

        // Define constants if not already defined
        if (!defined('COMPANY_NAME')) define('COMPANY_NAME', 'Employees Join This Month List');
        if (!defined('COMPANY_URLS')) define('COMPANY_URLS', ' ');

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(COMPANY_NAME);
        $pdf->SetTitle('Employees Join This Month List');

        // Set custom header with centered company name
        $pdf->setHeaderData('', 0, '', ''); // Clear default header
        $pdf->setPrintHeader(false); // Disable default header

        // Add a custom header manually
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 14);

        // Get page width
        $pageWidth = $pdf->getPageWidth();
        $margins = $pdf->getMargins();
        $usableWidth = $pageWidth - $margins['left'] - $margins['right'];

        $pdf->Cell($usableWidth, 5, COMPANY_NAME, 0, 1, 'C', 0, '', 0);
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 11);

        // Table header
        // $html = '<h3>Employees Join This Month</h3>';
        $html .= '<table border="1" cellpadding="5">
            <tr style="background-color:#f2f2f2;text-align:center;font-weight:bold;">
              <th>SI No</th>
               <th>Joining Date</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Designation</th>
            </tr>';

        // Table data
        $si = 1;
        foreach ($employees as $emp) {
            $html .= '<tr style="text-align:center;font: size 14px;">
            <td>' . $si++ . '</td>
                 <td>' . (isset($emp['ep']['joining_date']) ? $emp['ep']['joining_date'] : '') . '</td>
                <td>' . (isset($emp[0]['name']) ? $emp['0']['name'] : '') . '</td>
               
                 <td>' . (isset($emp['b']['branch']) ? $emp['b']['branch'] : '') . '</td>
                <td>' . (isset($emp['ep']['designation']) ? $emp['ep']['designation'] : '') . '</td>

              </tr>';
        }
        $html .= '</table>';

        // Write HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF for download
        $pdf->Output('Join This Month.pdf', 'D');
    }
    public function setupprofile($emp_pkey = 0)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        $this->set("user", $this->Session->read('company_code'));
        if ($emp_pkey) {
            //edit mode
            $this->set('emp_pkey', $emp_pkey);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            if (is_array($arr_emp_details['EmployeeDetails'])) {
                $this->set('arr_emp_details', $arr_emp_details['EmployeeDetails']);
            }


            $this->set('user_group', $user_group);

            //   debug($user_group);
            if (isset($user_group) && $user_group == 2) {
                //Employee
                $head = 'My Profile';
            } else {
                //Admin
                $head = $arr_emp_details['EmployeeDetails']['first_name'] . " " . $arr_emp_details['EmployeeDetails']['middile_name'] . " " . $arr_emp_details['EmployeeDetails']['last_name'] . "'s Profile";
            }

            $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_pkey and end_date_effective is null ");
            $this->set('arr_gross', $arr_gross);

            $this->set('head', $head);
            $this->loadEmpDetails($emp_pkey);

            $user_avatar = $this->EmployeeDetails->query("select avatar from user_credentials where emp_fkey = $emp_pkey ");
            $this->set('arr_user_avatar', $user_avatar);

            //Ends
            //Load employee professional details
            $this->loadEmpProfDetails($emp_pkey);
            //Ends
            //Load employee tax heads
            $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
            //Ends
        } else {
            //add mode
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }
            $arr_emp_personal_profile['nationality_id'] = "75"; //This is to set default nationality as INDIAN. by Arul P Das on 20-6-21

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }

            $this->set('head', 'New Employee');
            $this->set('emp_pkey', 0);
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            //debug($arr_emp_professional_profile);
            $this->set('arr_taxationinfo', array());
        }

        //Fetch countries by ARUL P DAS on 9/5/2021
        $arr_countries = $this->EmployeeDetails->query("SELECT * FROM countries_nationality order by id asc");
        $this->set('arr_countries', $arr_countries);

        //Fetch taxation fields for creating form dynamically
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        // $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $cur_emp_key = $this->Session->read("emp_fkey");
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        if (count($arr_branches) == 0) {
            //The below code is to check whether the user is employee or admin. if it is employeem then list his criteria branches only
            $user_group = $this->Session->read('user_group');
            $user = $this->Session->read('company_code');
            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );
                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }
        $this->set('arr_branches', $arr_branches);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        $emp_family = $this->EmployeeDetails->query("select * from emp_family where emp_fkey = '$emp_pkey' and is_nominee = 'Y' and status = 1");
        $this->set('emp_family', $emp_family);

        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);


        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $hierarchy_set = $this->NoticePeriod->query("select * from emp_details where emp_pkey in (select attr1 from emp_proff where emp_fkey = '$emp_pkey') ");
        $this->set('hierarchy_set', $hierarchy_set);
    }

    public function listimported()
    {
        $this->autoRender = FALSE;
        $user_group = $this->Session->read("user_group");
        $emp_pkeys = 0;
        $this->set('emp_pkeys', $emp_pkeys);
        //Admin view
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));

        $missed_prof = $this->checkProff();
        $this->set('active_emp_count', $active_emp_count);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        // debug($arr_Des);
        $this->set('missed_prof', $missed_prof);
        $this->render('listimported');
    }
    //edited by athira on 29-01-2025
    public function employeesunder()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read("user_group");
        $this->set('user_group', $user_group);
        $cur_emp_key = $this->Session->read("emp_fkey");
        $emp_pkey = $this->Session->read("emp_fkey");
        $company_code = strtoupper($this->Session->read('company_code'));
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey'
                )
            )
        );
        $conditions = array(
            array(
                'status' => 1,
                'EmployeeProffessional.attr1' => $emp_pkeys
            )
        );
        $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
        $this->set('active_emp_count', $active_emp_count);

        $this->set('emp_pkeys', $emp_pkeys);
        //Fetch Units for the company
        //commented by sinsiya
        //$arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        if ($company_code == 'VGFS' || $company_code == 'VSFS' || $company_code == 'DEMO') {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        }
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
            // Fetch Employee's Branch Code
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $this->set('is_ho', $is_ho);
            if ($is_ho != 1) {
                $emp_branch = $this->EmployeeDetails->query("SELECT branch_code, branch_name FROM branches WHERE branch_code = (SELECT branch_code FROM emp_details WHERE emp_pkey = $emp_pkey) LIMIT 1");
                $arr_branches = array(
                    array(
                        'branch_code' => $emp_branch[0]['branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['branches']['branch_name']
                    )
                );
            } else {
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
            }
        }


        //added by arul
        if (count($arr_branches) == 0) {
            $user_group = $this->Session->read('user_group');
            $user = $this->Session->read('company_code');
            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );
                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }
        $this->set('arr_branches', $arr_branches);


        // $arr_Emp=$this->MasterdataManagement->getEmployeeListForCombo();
        // $this->set('arr_Emp',$arr_Emp);

        $arr_Des = $this->MasterdataManagement->getDesignationsListForCombo();
        // debug($arr_Des);
        $this->set('arr_Des', $arr_Des);
        $this->render('index');
    }
    //end
    public function testfunction()
    {
        $this->autoRender = false;

        var_dump($ffd);
    }

    public function checkProff()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $missed_prof = $this->EmployeeDetails->query("select first_name from emp_details where status !=0 and emp_pkey not in (select emp_fkey from emp_proff)");
        return $missed_prof;
    }

    public function getstages($site_pkey = 0)
    {
        $this->autoRender = false;
        //$site_pkey=[];
        //  debug($site_pkey);
        $sitepkey_array = $site_pkey;
        if ($sitepkey_array == '') {
            $conditions = '';
        } else {
            $conditions = 'branch_code="' . $sitepkey_array . '"';
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code', 'conditions' => array('status' => 1, $conditions))));
        //debug($arr_stages);
        $str_stage_options_html = '';
        $this->set('arr_Emp', $arr_Emp);
        // debug($arr_Emp);
        foreach ($arr_Emp as $value) {
            $emp_pkey = isset($value['emp_pkey']) ? $value['emp_pkey'] : '';
            $emp_name = isset($value['emp_name']) ? $value['emp_name'] : '';
            $str_stage_options_html .= '<option value="' . $emp_pkey . '">' . $emp_name . '</option>';
            //  debug($str_stage_options_html);
        }
        echo $str_stage_options_html;
    }

    public function getautohierarchycompletions()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        $filter_condition = array();
        $emp = $arr_request_data['emp'];
        $searchkey = $arr_request_data['username'];
        $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR EmpName like '%" . $searchkey . "%' OR EmployeeDetails.emp_id like '%" . $searchkey . "%' and EmployeeDetails.emp_pkey not in ('$emp') ";

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'employee_info',
                'alias' => 'EmpInfo',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpInfo.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,concat(first_name," ",ifnull(last_name," ")," - ",employee_id) as emp_name,branch_code', 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        $arr_filterresult = array(
            //            array(
            ////                'emp_pkey' => '',
            ////                'emp_name' => 'ALL',
            //            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = array_merge(isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(), isset($val['0']) ? $val['0'] : array());
        }
        echo json_encode($arr_filterresult);
    }

    public function getautohierarchycompletionsvgfs()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array();
        $emp = $arr_request_data['emp'];
        $searchkey = $arr_request_data['username'];
        $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%' and emp_pkey not in ('$emp') ";

        //    debug($filter_condition);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => "emp_pkey,CONCAT(ifnull(first_name,''),' ', ifnull(last_name,''),' - ',`EmployeeProfessionalDetails`.`emp_company_id`) as emp_name,branch_code", 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'No Employees Found',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = array_merge(isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(), isset($val['0']) ? $val['0'] : array());
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function getautocompletions()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array('status' => "1");
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition[] = 'branch_code = "' . $branch . '" and first_name LIKE "%' . $searchkey . '%"';
        } else {
            $searchkey = $arr_request_data['username'];
            //            $filter_condition[] = 'first_name LIKE "%' . $searchkey . '%"';
            $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%'";
        }
        //    debug($filter_condition);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,concat(EmployeeDetails.first_name," ",last_name," ",EmployeeProfessionalDetails.emp_company_id) as emp_name,branch_code', 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'ALL',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val['0']) : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */



    public function listemployeesProfileImporteds()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $arr_requestdata = $this->request->data;
        $user_group = $this->Session->read("user_group");
        // debug($arr_request_data['branch']);
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        //debug($branch);
        $emp = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $des = isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '';
        //debug($branch);
        // debug($emp);
        $conditions = array("EmployeeDetails.status" => 4);
        //$conditions = array('EmployeeDetails.status != 0');
        if ($branch != '') {
            $conditions[] = 'EmployeeDetails.branch_code="' . $branch . '"';
        }
        // debug($branch);

        if ($emp != '') {

            $conditions[] = 'EmployeeDetails.emp_pkey="' . $emp . '"';
        }

        //   debug($emp);
        if ($des == '') {
            $des = '';
        } else {
            $des = 'EmployeeProfessionalDetails.designation="' . $des . '"';
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';

        $ofst = ($page - 1) * $limit;

        $fields = 'EmployeeDetails.status,Branches.branch_name,designation.desig_name, EmployeeDetails.classification, UserCredentials.avatar, designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",EmployeeDetails.first_name,EmployeeDetails.last_name) as name,EmployeeProfessionalDetails.designation,DATE_FORMAT(EmployeeProfessionalDetails.joining_date, "%d/%m/%Y") AS joining_date,EmployeeDetails.mobile_no,EmployeeDetails.date_of_birth,EmployeeDetails.email';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'designation',
                'alias' => 'designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1)
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1)
            ),
            array(
                'table' => 'user_credentials',
                'alias' => 'UserCredentials',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
            )
        );

        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        if (isset($arr_request_data['emp'])) {
            // $conditions[] = "(first_name like '%".$arr_request_data['emp']."%' OR EmployeeProfessionalDetails.emp_company_id like '%".$arr_request_data['emp']."%'  OR last_name like '%".$arr_request_data['emp']."%' OR emp_id like '%".$arr_request_data['emp']."%')";
            $conditions[] = "(EmployeeDetails.first_name like '%" . $arr_request_data['emp'] . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $arr_request_data['emp'] . "%'  OR EmployeeDetails.last_name like '%" . $arr_request_data['emp'] . "%')";
        }

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        if ($count == 0) { //This is to check whether employee have hierarchie employees or not. if no employee in hierarchie displays all employees. by ***ARUL P DAS on 5/3/2020
            if ($user_group == '2') {
                foreach ($conditions as $check_key => $check_val) {
                    if (strpos($check_val, 'EmployeeProfessionalDetails.attr1') !== false) {
                        unset($conditions[$check_key]);
                    }
                }
            }
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));

            if ($cur_emp_branch_find) {
                $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                $conditions[] = "EmployeeDetails.branch_code = '$cur_emp_branch' ";
            }

            $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        }
        $arr_emp = $this->EmployeeDetails->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $joins,
                "conditions" => $conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume/';

        foreach ($arr_emp as $key => $value) {

            $currentUserImage = "";

            if (isset($value['UserCredentials']['avatar']) && $value['UserCredentials']['avatar'] != "") {
                $currentUserImage = $this->webroot . $value['UserCredentials']['avatar'];
            } else {
                if (strtolower($value["EmployeeDetails"]['classification']) == "male") {
                    $currentUserImage = $this->webroot . "img/placeholdermen.jpeg";
                } else {
                    $currentUserImage = $this->webroot . "img/placeholderwomen.jpeg";
                }
            }

            $actions = array(
                "buttons" => "<a class='resumebutton' href='" . $baseResumeUrl . $value["EmployeeDetails"]['emp_pkey'] . "' ><li class='fa fa-file-pdf-o'></li></a>",
                "avatar" => "<img style='width: 28px; margin-left: 20px; border-radius: 5px; ' src='{$currentUserImage}' alt='image' />"
            );
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0], $value["designation"], $value["Branches"], $actions);
        }
        //debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }
    //edited by athira on 29-01-2025
//     public function listjoinedemployees()
//     {
//         $this->autoRender = false;
//         $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

//         // Pagination parameters
//         $page = isset($this->request->query['page']) ? $this->request->query['page'] : 1;
//         $rows = isset($this->request->query['rows']) ? $this->request->query['rows'] : 10;
//         $offset = ($page - 1) * $rows;
//     // Sorting parameters from EasyUI
//     $sort  = isset($this->request->query['sort']) ? $this->request->query['sort'] : 'name';
//     $order = isset($this->request->query['order']) ? $this->request->query['order'] : 'ASC';

//     // Allowed sortable fields mapping
//    // Allowed sortable fields mapping
// $allowedSort = [
//     'name'        => 'first_name',
//     'dob'         => 'date_of_birth',
//     'mail_id'     => 'email',
//     'phone_no'    => 'mobile_no',
//     'district'    => 'district'
// ];

// // Final sorting field
// $sortField = isset($allowedSort[$sort]) ? "EmployeeJoin." . $allowedSort[$sort] : "EmployeeJoin.first_name";


//         // Search parameter
//         $search = isset($this->request->query['emp']) ? trim($this->request->query['emp']) : '';

//         // Search conditions (name only)
//         $conditions = [];
//         if ($search !== '') {
//             $conditions['OR'] = [
//                 'EmployeeJoin.first_name LIKE' => "%$search%",
//                 'EmployeeJoin.last_name LIKE'  => "%$search%",
//                 'CONCAT(EmployeeJoin.first_name, " ", EmployeeJoin.last_name) LIKE' => "%$search%"
//             ];
//         }

//         // Total count with conditions
//         $total = $this->EmployeeJoin->find('count', [
//             'conditions' => array_merge($conditions, [
//                 'EmployeeJoin.status' => 1
//             ])
//         ]);

//         // Fetch paginated employees
//         $employees = $this->EmployeeJoin->find('all', [
//             'conditions' => array_merge($conditions, [
//                 'EmployeeJoin.status' => 1
//             ]),
//             'limit'  => $rows,
//             'offset' => $offset,
//             'order' => [$sortField => $order]
//         ]);
//         $data = [];
//         foreach ($employees as $empRow) {
//             $emp = $empRow['EmployeeJoin'];
//             $avatar = '';
//             if (!empty($emp['profile_image_url'])) {
//                 $avatar = '<img src="/' . $emp['profile_image_url'] . '" style="height:20px;width:30px;border-radius:50%;" />';
//             } else {
//                 $avatar = '<img src="/img/placeholdermen.jpeg' . '" style="height:20px;width:30px;border-radius:50%;" />';
//             }

//             $data[] = [
//                 'emp_pkey' => $emp['emp_join_pkey'],
//                 'name'     => $emp['first_name'] . ' ' . $emp['last_name'],
//                 'dob'      => !empty($emp['date_of_birth']) ? date('d-m-Y', strtotime($emp['date_of_birth'])) : '',
//                 'phone_no' => $emp['mobile_no'],
//                 'mail_id'  => $emp['email'],
//                 'district' => $emp['district'],
//                 'avatar'   => $avatar,
//             ];
//         }

//         // Return JSON
//         $response = [
//             'total' => $total,
//             'rows'  => $data,
//         ];

//         echo json_encode($response);
//         $this->response->type('json');
//     }
public function listjoinedemployees()
{
    $this->autoRender = false;
    $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

    // Pagination parameters
    $page = isset($this->request->query['page']) ? $this->request->query['page'] : 1;
    $rows = isset($this->request->query['rows']) ? $this->request->query['rows'] : 10;
    $offset = ($page - 1) * $rows;

    // Sorting parameters from EasyUI
    $sort  = isset($this->request->query['sort']) ? $this->request->query['sort'] : null;
    $order = isset($this->request->query['order']) ? strtoupper($this->request->query['order']) : null;

    // Allowed sortable fields mapping
    $allowedSort = [
        'name'         => 'first_name',
        'dob'          => 'date_of_birth',
        'mail_id'      => 'email',
        'phone_no'     => 'mobile_no',
        'district'     => 'district',
        'emp_join_pkey'=> 'emp_join_pkey'
    ];

    // Determine sort field and order
    if ($sort !== null && isset($allowedSort[$sort])) {
        $sortField = "EmployeeJoin." . $allowedSort[$sort];
        // Validate order, fallback to ASC if invalid
        $sortOrder = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';
    } else {
        // Default sort by last inserted first
        $sortField = "EmployeeJoin.emp_join_pkey";
        $sortOrder = "DESC";
    }

    // Search parameter
    $search = isset($this->request->query['emp']) ? trim($this->request->query['emp']) : '';

    // Search conditions (name only)
    $conditions = [];
    if ($search !== '') {
        $conditions['OR'] = [
            'EmployeeJoin.first_name LIKE'  => "%$search%",
            'EmployeeJoin.last_name LIKE'   => "%$search%",
            'CONCAT(EmployeeJoin.first_name, " ", EmployeeJoin.last_name) LIKE' => "%$search%"
        ];
    }

    // Total count with conditions
    $total = $this->EmployeeJoin->find('count', [
        'conditions' => array_merge($conditions, [
            'EmployeeJoin.status' => 1
        ])
    ]);

    // Fetch paginated employees
    $employees = $this->EmployeeJoin->find('all', [
        'conditions' => array_merge($conditions, [
            'EmployeeJoin.status' => 1
        ]),
        'limit'  => $rows,
        'offset' => $offset,
        'order'  => [$sortField => $sortOrder]
    ]);

    $data = [];
    foreach ($employees as $empRow) {
        $emp = $empRow['EmployeeJoin'];
        $avatar = '';
        if (!empty($emp['profile_image_url'])) {
            $avatar = '<img src="/' . $emp['profile_image_url'] . '" style="height:20px;width:30px;border-radius:50%;" />';
        } else {
            $avatar = '<img src="/img/placeholdermen.jpeg" style="height:20px;width:30px;border-radius:50%;" />';
        }

        $data[] = [
            'emp_pkey' => $emp['emp_join_pkey'],
            'name'     => trim($emp['first_name'] . ' ' . $emp['last_name']),
            'dob'      => !empty($emp['date_of_birth']) ? date('d-m-Y', strtotime($emp['date_of_birth'])) : '',
            'phone_no' => $emp['mobile_no'],
            'mail_id'  => $emp['email'],
            'district' => $emp['district'],
            'avatar'   => $avatar,
        ];
    }

    // Return JSON response
    $response = [
        'total' => $total,
        'rows'  => $data,
    ];

    $this->response->type('json');
    echo json_encode($response);
    exit; // good practice to stop further output
}

 




    public function listemployees()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $user_group = $this->Session->read("user_group");
        $cur_emp_key = $this->Session->read("emp_fkey");
        $company_code = strtoupper($this->Session->read('company_code'));

        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // Get filters from request
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $emp = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : (isset($arr_request_data['emp']) ? $arr_request_data['emp'] : '');
        $des = isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '';

        $filter = $this->request->data('filter') ?: $this->request->query('filter');
        $branch = $this->request->data('branch') ?: $this->request->query('branch');

        $conditions = [];

        // Branch filter
        if (!empty($branch)) {
            $conditions['EmployeeDetails.branch_code'] = $branch;
        }

        // Status filters
        //  <!-- edited by BINDU 25-03-2026 -->
        if ($filter === "2") {
            $conditions['EmployeeDetails.status'] = 2; // resigned
        } elseif ($filter === "active" || empty($filter)) {
            $conditions['EmployeeDetails.status'] = 1; // active
        } elseif ($filter === "notice") {
            $conditions[] = "CURDATE() <= Termination.last_approved_working_date";
            $conditions['Termination.status'] = 1;
            $conditions['EmployeeDetails.status'] = 1;
        } elseif ($filter === "this_month") {
            $conditions['EmployeeProfessionalDetails.joining_date >='] = date('Y-m-01');
            $conditions['EmployeeProfessionalDetails.joining_date <='] = date('Y-m-t');
            $conditions['EmployeeDetails.status'] = 1;
        } elseif ($filter === "previous_month") {
            $conditions['EmployeeProfessionalDetails.joining_date >='] = date('Y-m-01', strtotime('first day of last month'));
            $conditions['EmployeeProfessionalDetails.joining_date <='] = date('Y-m-t', strtotime('last day of last month'));
            $conditions['EmployeeDetails.status'] = 1;
        }
        // <!-- edited by BINDU 25-03-2026 END -->

        // Designation filter
        if ($des != '') {
            $conditions['EmployeeProfessionalDetails.designation'] = $des;
        }

        // ✅ Global search filter for name OR emp_company_id OR mobile_no
        if (!empty($emp)) {
            $conditions['OR'] = [
                'EmployeeDetails.first_name LIKE' => "%$emp%",
                'EmployeeProfessionalDetails.emp_company_id LIKE' => "%$emp%"
            ];
        }

        // Pagination
        $limit = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 10;
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
        $ofst = ($page - 1) * $limit;

        $fields = 'EmployeeDetails.status, Branches.branch_name, designation.desig_name,
               EmployeeDetails.classification, UserCredentials.avatar,
               designation.desig_code, emp_pkey,
               EmployeeProfessionalDetails.emp_company_id,
               EmployeeDetails.first_name as name,
               EmployeeProfessionalDetails.designation,
               DATE_FORMAT(EmployeeProfessionalDetails.joining_date, "%d-%m-%Y") AS joining_date,
               mobile_no';

        $joins = [
            [
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'conditions' => ['EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey']
            ],
            [
                'table' => 'designation',
                'alias' => 'designation',
                'type' => 'LEFT',
                'conditions' => ['EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1]
            ],
            [
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'conditions' => ['EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1]
            ],
            [
                'table' => 'user_credentials',
                'alias' => 'UserCredentials',
                'type' => 'LEFT',
                'conditions' => ['EmployeeDetails.emp_pkey = UserCredentials.emp_fkey']
            ],
            [
                'table' => 'termination',
                'alias' => 'Termination',
                'type' => 'LEFT',
                'conditions' => ['EmployeeDetails.emp_pkey = Termination.emp_fkey']
            ],
        ];


        // Count total rows
        $count = $this->EmployeeDetails->find("count", [
            'joins' => $joins,
            "conditions" => $conditions,
            "fields" => "DISTINCT EmployeeDetails.emp_pkey"

        ]);







        // Fetch employee records
       $arr_emp = $this->EmployeeDetails->find("all", [
    'fields'     => $fields,
    'joins'      => $joins,
    "conditions" => $conditions,
    'group'      => ['EmployeeDetails.emp_pkey'],
    'order'      => [$sort => $order],
    'limit'      => $limit,
    'offset'     => $ofst
]);

        // Prepare response
        $resp_emp = [];
        $resp_emp["rows"] = [];
        $resp_emp["total"] = $count;

        $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume/';

        foreach ($arr_emp as $key => $value) {
            $currentUserImage = !empty($value['UserCredentials']['avatar'])
                ? $this->webroot . $value['UserCredentials']['avatar']
                : (strtolower($value["EmployeeDetails"]['classification']) == "male"
                    ? $this->webroot . "img/placeholdermen.jpeg"
                    : $this->webroot . "img/placeholderwomen.jpeg");

            $actions = [
                "buttons" => "<a class='resumebutton' href='" . $baseResumeUrl . $value["EmployeeDetails"]['emp_pkey'] . "'><li class='fa fa-file-pdf-o'></li></a>",
                "avatar" => "<img style='width: 28px; margin-left: 20px; border-radius: 5px;' src='{$currentUserImage}' alt='image' />"
            ];

            // Calculate profile completion percentage
            $completion = $this->calculateOnboardingPercentage($value["EmployeeDetails"]['emp_pkey']);

            $resp_emp["rows"][$key] = array_merge(
                $value["EmployeeDetails"],
                $value["EmployeeProfessionalDetails"],
                $value[0],
                $value["designation"],
                $value["Branches"],
                $actions,
                ['profile_completion' => $completion]
            );
        }
        // debug($resp_emp);

        echo json_encode($resp_emp);
    }

//   public function listjoinedemployees()
//     {
//         $this->autoRender = false;
//         $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

//         // Pagination parameters
//         $page = isset($this->request->query['page']) ? $this->request->query['page'] : 1;
//         $rows = isset($this->request->query['rows']) ? $this->request->query['rows'] : 5;
//         $offset = ($page - 1) * $rows;

//         // Search parameter
																						 
//         $search = isset($this->request->query['emp']) ? trim($this->request->query['emp']) : '';

//         // Search conditions
//         $conditions = [];
//         $joins = [];
        
//         if ($search !== '') {
//             $joins[] = [
//                 'table' => 'emp_proff',
//                 'alias' => 'EmployeeProfessionalDetails',
//                 'type'  => 'LEFT',
//                 'conditions' => ['EmployeeJoin.emp_join_pkey = EmployeeProfessionalDetails.emp_fkey']
//             ];
            
//             $conditions['OR'] = [
//                 'EmployeeJoin.first_name LIKE' => "%$search%",
//                 'EmployeeJoin.last_name LIKE'  => "%$search%",
//                 'CONCAT(EmployeeJoin.first_name, " ", EmployeeJoin.last_name) LIKE' => "%$search%",
//                 'EmployeeProfessionalDetails.emp_company_id LIKE' => "%$search%"
//             ];
//         }

//         // Total count with conditions
//         $total = $this->EmployeeJoin->find('count', [
//             'joins'      => $joins,
//             'conditions' => array_merge($conditions, [
//                 'EmployeeJoin.status' => 1
//             ])
//         ]);
												  
							
	 

//         // Fetch paginated employees
//         $employees = $this->EmployeeJoin->find('all', [
//             'joins'      => $joins,
//             'fields'     => ['EmployeeJoin.*', 'EmployeeProfessionalDetails.emp_company_id'],
//             'conditions' => array_merge($conditions, [
//                 'EmployeeJoin.status' => 1
//             ]),
//             'limit'  => $rows,
//             'offset' => $offset,
//             'order'  => ['EmployeeJoin.first_name' => 'ASC']
//         ]);
//         $data = [];
//         foreach ($employees as $empRow) {
//             $emp = $empRow['EmployeeJoin'];
//             $emp_company_id = isset($empRow['EmployeeProfessionalDetails']['emp_company_id']) ? $empRow['EmployeeProfessionalDetails']['emp_company_id'] : '';
            
//             $avatar = '';
//             if (!empty($emp['profile_image_url'])) {
//                 $avatar = '<img src="/' . $emp['profile_image_url'] . '" style="height:40px;width:40px;border-radius:50%;" />';
//             }

//             $data[] = [
//                 'emp_pkey' => $emp['emp_join_pkey'],
//                 'emp_company_id' => $emp_company_id,
//                 'name'     => $emp['first_name'] . ' ' . $emp['last_name'],
//                 'dob'      => !empty($emp['date_of_birth']) ? date('d-m-Y', strtotime($emp['date_of_birth'])) : '',
//                 'phone_no' => $emp['mobile_no'],
//                 'mail_id'  => $emp['email'],
//                 'district' => $emp['district'],
	 

								  
												 
												  
									  
		  
	   

								
												   
												  
									  
		   
						  
							
											  
	   

			   
									 
									   
					 
												
																														   
				
//                 'avatar'   => $avatar,
//             ];
//         }

//         // Return JSON
//         $response = [
//             'total' => $total,
//             'rows'  => $data,
											
										
										   
								  
//         ];

//         echo json_encode($response);
//         $this->response->type('json');
//     }

						   
				 
						  
						 
	  

								  
								
												 
 

    // public function listemployees()
    // {
    //     $this->autoRender = FALSE;
    //     $arr_request_data = $this->request->data;
    //     $user_group       = $this->Session->read("user_group");
    //     $cur_emp_key      = $this->Session->read("emp_fkey");
    //     $company_code     = strtoupper($this->Session->read('company_code'));

    //     $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeDetails->useDbConfig             = $this->Session->read('ds');

    //     // Get filters from request
    //     $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
    //     $emp    = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : (isset($arr_request_data['emp']) ? $arr_request_data['emp'] : '');
    //     $des    = isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '';

    //     $filter = $this->request->data('filter') ?: $this->request->query('filter');
    //     $branch = $this->request->data('branch') ?: $this->request->query('branch');

    //     $conditions = [];

    //     // Branch filter
    //     if (!empty($branch)) {
    //         $conditions['EmployeeDetails.branch_code'] = $branch;
    //     }

    //     // Status filters
    //     if ($filter === "2") {
    //         $conditions['EmployeeDetails.status'] = 2; // resigned
    //     } elseif ($filter === "active" || empty($filter)) {
    //         $conditions['EmployeeDetails.status'] = 1; // active
    //     } elseif ($filter === "notice") {
						   
										  
											  
    //         $conditions[] = "CURDATE() <= DATE_ADD(Termination.last_approved_working_date, INTERVAL EmployeeProfessionalDetails.notice_days DAY)";
    //         $conditions['Termination.status'] = 1;
    //     }

    //     // Designation filter
    //     if ($des != '') {
    //         $conditions['EmployeeProfessionalDetails.designation'] = $des;
    //     }

    //     // ✅ Global search filter for name OR emp_company_id OR mobile_no
    //     if (!empty($emp)) {
    //         $conditions['OR'] = [
    //             'EmployeeDetails.first_name LIKE' => "%$emp%",
    //             'EmployeeDetails.last_name LIKE'  => "%$emp%",
    //             'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name) LIKE' => "%$emp%",
    //             'EmployeeProfessionalDetails.emp_company_id LIKE' => "%$emp%",
    //             // 'EmployeeDetails.mobile_no LIKE' => "%$emp%"
    //         ];
    //     }

    //     // Pagination
    //     $limit = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 10;
    //     $page  = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    //     $sort  = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_pkey';
    //     $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
    //     $ofst  = ($page - 1) * $limit;

    //     $fields = 'EmployeeDetails.status, Branches.branch_name, designation.desig_name,
    //            EmployeeDetails.classification, UserCredentials.avatar,
    //            designation.desig_code, emp_pkey,
    //            EmployeeProfessionalDetails.emp_company_id,
    //            CONCAT_WS(" ",EmployeeDetails.first_name,EmployeeDetails.last_name) as name,
    //            EmployeeProfessionalDetails.designation,
    //            DATE_FORMAT(EmployeeProfessionalDetails.joining_date, "%d-%m-%Y") AS joining_date,
    //            mobile_no';

    //     $joins = [
    //         [
    //             'table' => 'emp_proff',
    //             'alias' => 'EmployeeProfessionalDetails',
    //             'type'  => 'LEFT',
    //             'conditions' => ['EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey']
    //         ],
    //         [
    //             'table' => 'designation',
    //             'alias' => 'designation',
    //             'type'  => 'LEFT',
    //             'conditions' => ['EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1]
    //         ],
    //         [
    //             'table' => 'branches',
    //             'alias' => 'Branches',
    //             'type'  => 'LEFT',
    //             'conditions' => ['EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1]
    //         ],
    //         [
    //             'table' => 'user_credentials',
    //             'alias' => 'UserCredentials',
    //             'type'  => 'LEFT',
    //             'conditions' => ['EmployeeDetails.emp_pkey = UserCredentials.emp_fkey']
    //         ],
    //         [
    //             'table' => 'termination',
    //             'alias' => 'Termination',
    //             'type'  => 'LEFT',
    //             'conditions' => ['EmployeeDetails.emp_pkey = Termination.emp_fkey']
    //         ],
    //     ];


    //     // Count total rows
    //     $count = $this->EmployeeDetails->find("count", [
    //         'joins'      => $joins,
    //         "conditions" => $conditions,
    //         "fields"     => "DISTINCT EmployeeDetails.emp_pkey"

    //     ]);
										  
														   
									  
										   
																  
			  

    //     // Fetch employee records
    //     $arr_emp = $this->EmployeeDetails->find("all", [
    //         'fields'     => $fields,
    //         'joins'      => $joins,
    //         "conditions" => $conditions,
    //         'order'      => [$sort => $order],
														 
    //         'limit'      => $limit,
    //         'offset'     => $ofst
    //     ]);

    //     // Prepare response
    //     $resp_emp = [];
    //     $resp_emp["rows"] = [];
    //     $resp_emp["total"] = $count;

    //     $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume/';

    //     foreach ($arr_emp as $key => $value) {
    //         $currentUserImage = !empty($value['UserCredentials']['avatar'])
    //             ? $this->webroot . $value['UserCredentials']['avatar']
    //             : (strtolower($value["EmployeeDetails"]['classification']) == "male"
    //                 ? $this->webroot . "img/placeholdermen.jpeg"
    //                 : $this->webroot . "img/placeholderwomen.jpeg");

    //         $actions = [
    //             "buttons" => "<a class='resumebutton' href='" . $baseResumeUrl . $value["EmployeeDetails"]['emp_pkey'] . "'><li class='fa fa-file-pdf-o'></li></a>",
    //             "avatar"  => "<img style='width: 28px; margin-left: 20px; border-radius: 5px;' src='{$currentUserImage}' alt='image' />"
    //         ];

    //         $resp_emp["rows"][$key] = array_merge(
    //             $value["EmployeeDetails"],
    //             $value["EmployeeProfessionalDetails"],
    //             $value[0],
    //             $value["designation"],
    //             $value["Branches"],
    //             $actions
    //         );
    //     }
    //     // debug($resp_emp);

    //     echo json_encode($resp_emp);
    // }


    //end
    //branch filtter     
    public function jsons($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = " and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='" . $cur_emp_branch . "'";
        }

        //employee branch wise sorting ends here

        if ($q != null) {
            $q_condition = "and (first_name like '%$q%' or emp_proff.emp_company_id like '%$q%') ";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 $branch_condition $q_condition ORDER BY first_name ASC ");
        // debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function jsonss($emp = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;

        if ($q != null) {
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='$cur_emp_branch' ";
        } else {
            $branch_condition = "";
        }
        //employee branch wise sorting ends here

        $branch_array = $this->EmployeeDetails->query("select * from emp_details  where status = 1 $q_condition $branch_condition and emp_pkey != $emp ");

        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "SELECT");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function removeProfileImage($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $query = $this->qualifcations->query("UPDATE `user_credentials` SET `avatar` = null WHERE `emp_fkey` = '$pkey'");
        if ($query !== false) {
            $success = 1;
        } else {
            $success = 0;
        }
        echo json_encode($success);
    }

    public function jsonsb($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "name like '%$q%'";
        } else {
            $q_condition = "";
        }
        $q_condition = "status = '1' ";
        $branch_array = $this->EmployeeDetails->query("select * from emp_banks Where $q_condition  ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = "";
        //        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch .= '<option >' . $value['emp_banks']['name'];
        }


        //        $array['items'] = $branch;
        echo json_encode($branch);
    }


    public function setup($emp_pkey = 0)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        $company_code = $this->Session->read('company_code');
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
        $this->Education->useDbConfig = $this->Session->read('ds');
        $this->WorkExperience->useDbConfig = $this->Session->read('ds');

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        $this->set('plan', $plan);
        $this->set('company_code', $company_code);
        //end
        //$company_code = $this->Session->read('company_code');

        $company_code = strtoupper($this->Session->read('company_code'));
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        $this->set('user_group', $user_group);
        // debug($emp_pkey);
        //  exit;
        $arr_emp_details['EmployeeJoin'] = '';
        if ($emp_pkey) {
            //edit mode
            $this->set('emp_pkey', $emp_pkey);
            $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
            $arr_emp_details = $this->EmployeeJoin->find('first', array('conditions' => array('emp_join_pkey' => $emp_pkey)));
            // if (is_array($arr_emp_details['EmployeeJoin'])) {
            //     $this->set('arr_emp_details', $arr_emp_details['EmployeeJoin']);
            // }

            if (isset($arr_emp_details['EmployeeJoin']) && is_array($arr_emp_details['EmployeeJoin'])) {
    $this->set('arr_emp_details', $arr_emp_details['EmployeeJoin']);
}
            // debug($arr_emp_details);
            $this->set('user_group', $user_group);

            //   debug($user_group);
            if (isset($user_group) && $user_group == 2) {
                //Employee
                $head = 'My Profile';
            } else {
                //Admin
                $head = $arr_emp_details['EmployeeJoin']['first_name'] . " "  . $arr_emp_details['EmployeeJoin']['last_name'] . "'s Profile";
            }

            $empJoinRow = $this->EmployeeDetails->query("
        SELECT emp_fkey 
        FROM emp_join 
        WHERE emp_join_pkey = {$emp_pkey}
    ");

            if (!empty($empJoinRow[0]['emp_join']['emp_fkey'])) {
                $emp_pkey = $empJoinRow[0]['emp_join']['emp_fkey']; // now 1121
            }
            $this->set('emp_pkey', $emp_pkey);
            // debug($arr_emp_detail);

            $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_pkey and end_date_effective is null ");
            $this->set('arr_gross', $arr_gross);

            $this->set('head', $head);
            $this->loadEmpDetails($emp_pkey);

            $user_avatar = $this->EmployeeDetails->query("select avatar from user_credentials where emp_fkey = $emp_pkey ");
            $this->set('arr_user_avatar', $user_avatar);

            //Ends
            //Load employee professional details
            $this->loadEmpProfDetails($emp_pkey);
            //Ends
            //Load employee tax heads
            // $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
            if ($user_group == 2) {
                $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$emp_pkey'");
                $this->set('payroUser', $payroUser);
            }
        } else {
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }
            $arr_emp_personal_profile['nationality_id'] = "75"; //This is to set default nationality as INDIAN. by Arul P Das on 20-6-21

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }
            if ($this->Session->read('company_code') == 'DEMO' || $this->Session->read('company_code') == 'KWMT') {
                $arr_emp_professional_profile['emp_category'] = '';
            }
            $this->set('head', 'New Employee');
            $this->set('emp_pkey', 0);
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            // debug($arr_emp_professional_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            $this->set('arr_taxationinfo', array());
        }

        //     $educationDetails = $this->Education->find('all', [
        //     'conditions' => ['Education.emp_join_fkey' => $emp_pkey, 'Education.status' => 1]
        // ]);

        //  $experienceDetails = $this->WorkExperience->find('all', [
        //     'conditions' => ['WorkExperience.emp_join_fkey' => $emp_pkey, 'WorkExperience.status' => 1]
        // ]);

        // $this->set('educationData',$educationDetails);
        //  $this->set('experienceData',$experienceDetails);

        // $this->set('arr_personalinfo', $arr_emp_details['EmployeeJoin']);
        $this->set('arr_personalinfo', isset($arr_emp_details['EmployeeJoin']) ? $arr_emp_details['EmployeeJoin'] : []);


        //Fetch countries by ARUL P DAS on 9/5/2021
        $arr_countries = $this->EmployeeDetails->query("SELECT * FROM countries_nationality order by id asc");
        $this->set('arr_countries', $arr_countries);

        //Fetch taxation fields for creating form dynamically
        // $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);
        // debug($arr_departments);
        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);
        // debug($arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);
        // debug($arr_grades);

        //Edited by Akshay on 10-10-2023
        //Fetch category
        if ($company_code == 'DEMO' || $company_code == 'KWMT') {
            $arr_category = $this->EmployeeDetails->query("SELECT category_pkey,category_code,category_name FROM category WHERE status = 1 AND category_pkey in (SELECT category_fkey FROM grade WHERE status = 1 AND category_fkey != 0) ORDER BY category_name ASC;");
            $this->set('arr_category', $arr_category);
        }

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        // $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user = strtoupper($this->Session->read('company_code'));
        //$arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        //edited by sinsiya to display full branches for admin split
        if ($company_code == 'VGFS' || $company_code == 'DEMO' || $company_code == 'VSFS' || $company_code == 'GLET') {

            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        }
        //edited by sinsiya to display full branches
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        if (count($arr_branches) == 0) {
            //The below code is to check whether the user is employee or admin. if it is employeem then list his criteria branches only
            $user_group = $this->Session->read('user_group');

            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );

                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }

        $this->set('arr_branches', $arr_branches);
        $this->set('user', $user);
        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        // $emp_family = $this->EmployeeDetails->query("select * from emp_family where emp_fkey = '$emp_pkey' and is_nominee = 'Y' and status = 1");
        // $this->set('emp_family', $emp_family);

        //Fetch Units for the company
        // $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' 
        //                                             and day_time_seq not in (SELECT multishift FROM emp_proff WHERE emp_fkey = '$emp_pkey'
        //                                             and multishift is not null)
        //                                             "); //Edited by Akshay on 12-9-2024
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);


        //Fetch Units for the company
        //        $arr_hierarchy = $this->EmployeeDetails->query("select emp_pkey,concat(first_name,' ',last_name) as fullname from emp_details where status = 1");
        //        $this->set('arr_hierarchy', $arr_hierarchy);
        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);


        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        // $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        // $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $hierarchy_set = $this->NoticePeriod->query("select * from emp_details where emp_pkey in (select attr1 from emp_proff where emp_fkey = '$emp_pkey') ");
        $this->set('hierarchy_set', $hierarchy_set);
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_pkey` = '$emp_pkey'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        $yearmonth = $monthdd;
        // $queryResult = $this->EmployeeDetails->query("SELECT  time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')");
        // if (!($queryResult)) {
        //     return false;
        // }
        //edited by athira on 29-04-2025
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $branch = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='$emp_pkey' ");
        $branch_code = 'NULL';
        $month = date('Y-m-01');
        // $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
            return FALSE;
            die();
        }
        // if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
        //     if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // } else {
        //     if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // }



        //end
    }

    //Edited by Akshay on 10-10-2023
    public function getGrade($category_pkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');

        $arr_grade = array();
        if ($company_code == 'DEMO' || $company_code == 'KWMT') {
            if ($category_pkey != 0) {
                $arr_grade = $this->EmployeeDetails->query("SELECT grade_pkey, grade_code, grade_name, pay_scale FROM grade WHERE category_fkey = $category_pkey AND status = 1 ORDER BY grade_name ASC;");
            }
            echo json_encode($arr_grade);
        }
    }

    /*
     * Show tax Head Details form
     */

    public function showtaxheaddetail($emp_pkey = 0, $tax_heads_fkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
        $this->set('tax_heads_fkey', $tax_heads_fkey);
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        $tax_head = Set::extract('/TaxHead/.', $this->TaxHead->find("first", array('conditions' => array('tax_heads_pkey' => $tax_heads_fkey))));
        $tax_head_name = isset($tax_head[0]['tax_name']) ? $tax_head[0]['tax_name'] : 'Details';
        $this->set('tax_head_name', $tax_head_name);

        $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$tax_heads_fkey");
        $arr_emptaxtransactions = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$emp_pkey/$tax_heads_fkey");
        $arr_emptaxdocuments = $this->requestAction("/Taxation/loadEmpTaxHeadDocuments/$emp_pkey/$tax_heads_fkey");

        $companycode = $this->Session->read('company_code');

        $this->set('companycode', $companycode);
        $this->set('arr_emptaxdocuments', $arr_emptaxdocuments);
        $this->set('arr_taxheaddetails', $arr_taxheaddetails);
        // debug($arr_emptaxdocuments);
        $this->set('arr_emptaxtransactions', $arr_emptaxtransactions);
        $locked_data = $this->EmployeeTaxTransactions->query("select locked from emp_tax_transactions where emp_fkey =$emp_pkey and tax_heads_fkey= $tax_heads_fkey and tax_heads_details_fkey = 0");
        $locked = isset($locked_data['0']['emp_tax_transactions']['locked']) ? $locked_data['0']['emp_tax_transactions']['locked'] : 'N';
        $this->set('locked', $locked);
    }

    //Ends
    public function loadEmpDetails($emp_pkey = 0)
    {
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_personal_profile = $this->EmployeeDetails->find('first', [
                'conditions' => ['emp_pkey' => $emp_pkey]
            ]);

            if (isset($arr_emp_personal_profile['EmployeeDetails'])) {
                $emp = $arr_emp_personal_profile['EmployeeDetails'];

                // Build full name safely
                $full_name = '';
                if (isset($emp['first_name'])) {
                    $full_name .= $emp['first_name'];
                }
                if (isset($emp['middile_name']) && $emp['middile_name'] != '') {
                    $full_name .= ' ' . $emp['middile_name'];
                }
                if (isset($emp['last_name']) && $emp['last_name'] != '') {
                    $full_name .= ' ' . $emp['last_name'];
                }

                $emp['first_name'] = $full_name;
                $emp['name_as_per_bank'] = (isset($emp['name_as_per_bank']) && $emp['name_as_per_bank'] != '') ? $emp['name_as_per_bank'] : $full_name;
                $emp['name_as_on_aadhaar'] = (isset($emp['name_as_on_aadhaar']) && $emp['name_as_on_aadhaar'] != '') ? $emp['name_as_on_aadhaar'] : $full_name;
                $emp['name_as_on_pan'] = (isset($emp['name_as_on_pan']) && $emp['name_as_on_pan'] != '') ? $emp['name_as_on_pan'] : $full_name;

                $this->set('arr_personalinfo', $emp);
            } else {
                $this->set('arr_personalinfo', []); // record not found
            }
        } else {
            $this->set('arr_personalinfo', []); // invalid emp_pkey
        }
    }


    public function loadEmpProfDetails($emp_pkey = '')
    {
        $company_code = $this->Session->read('company_code');
        $user_group = $this->Session->read("user_group");

        $empJoinRow = $this->EmployeeDetails->query("
        SELECT emp_fkey 
        FROM emp_join 
        WHERE emp_join_pkey = {$emp_pkey}
    ");

        if (!empty($empJoinRow[0]['emp_join']['emp_fkey'])) {
            $emp_pkey = $empJoinRow[0]['emp_join']['emp_fkey']; // now 1121
        }


        //edited by athira on 21-02-2025
        $promo_status_query = $this->EmployeeProfessionalDetails->query("SELECT * FROM promotions WHERE  emp_fkey=$emp_pkey ");
        $promo_status = isset($promo_status_query[0]['promotions']['promotion_status']) ? $promo_status_query[0]['promotions']['promotion_status'] : '';
        //end
        if ($user_group == 2) {
            $emp_pkey = $emp_pkey; //$sessionObj['emp_fkey'];  
        }

        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            //Edited by Akshay 12/7/2023
            $arr_end_date = $this->EmployeeProfessionalDetails->query("SELECT contract_end_date FROM contracted_days WHERE emp_fkey = $emp_pkey");
            //edited by athira on 21-02-2025
            $arr_end_date2 = $this->EmployeeProfessionalDetails->query("SELECT end_date_effective FROM contracted_days WHERE emp_fkey = $emp_pkey");
            if ($promo_status == 'APPROVED') {
                $contract_end_date = isset($arr_end_date2[0]['contracted_days']['end_date_effective']) ? $arr_end_date2[0]['contracted_days']['end_date_effective'] : '';
            } else {
                $contract_end_date = isset($arr_end_date[0]['contracted_days']['contract_end_date']) ? $arr_end_date[0]['contracted_days']['contract_end_date'] : '';
            }

            //end
            if (empty($arr_emp_professional_profile)) {
                $empId = '';
                $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('first', array('order' => array('emp_proff_pkey' => 'DESC')));
                if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                    $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $arr_emp_id = explode($str_company_code, $str_emp_id);
                    if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                        $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                    } else {
                        $emp_id = '';
                    }
                } else {
                    //He is the first employee                  
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $emp_id = $str_company_code . "1000";
                }
                $arr_emp_professional_profile['emp_id'] = $emp_id;
                $arr_emp_professional_profile['emp_fkey'] = $emp_pkey;
            } else {
                $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            }
            if ($contract_end_date != '') {
                $arr_emp_professional_profile['end_date'] = $contract_end_date;
            }


            //Edited by Akshay on 11-10-2023
            if ($company_code == 'DEMO' || $company_code == 'KWMT' || $company_code == 'GLET') {
                $grade = isset($arr_emp_professional_profile['emp_grade']) ? $arr_emp_professional_profile['emp_grade'] : '';

                if ($grade != '') {
                    $arr_category = $this->EmployeeProfessionalDetails->query("SELECT category_fkey FROM grade WHERE grade_pkey = $grade AND status = 1 AND category_fkey != 0");
                }
                $category_pkey = isset($arr_category[0]['grade']['category_fkey']) ? $arr_category[0]['grade']['category_fkey'] : '';

                if ($category_pkey != '') {
                    $arr_emp_professional_profile['emp_category'] = $category_pkey;
                } else {
                    $arr_emp_professional_profile['emp_category'] = '';
                }
            }
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
        } else {
            $this->set('arr_professionalinfo', array());
        }
    }

    public function Finyear()
    {
        $this->autoRender = false;
    }

    public function promotion($emp_fkey = 0)
    {
        $this->set("emp_pkey", $emp_fkey);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        //Fetch Units for the company
        $arr_emp_structures = $this->EmployeeDetails->query("select * from employee_structure_vview where emp_pkey = $emp_fkey ");
        $this->set('arr_emp_structures', $arr_emp_structures);

        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_fkey and end_date_effective is null ");
        $this->set('arr_gross', $arr_gross);

        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_fkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $arr_empdetails = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_fkey)));
        $arr_emp_personal_profile = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
        foreach ($arr_personalinfokeys as $key) {
            $arr_emp_personal_profile[$key] = '';
        }

        $arr_professionalinfokeys = array();
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
        foreach ($arr_professionalinfokeys as $key) {
            $arr_emp_professional_profile[$key] = '';
        }
        $this->set('arr_personalinfo', $arr_emp_personal_profile);
        $this->set('arr_empdetails', $arr_empdetails);
        //        debug($arr_emp_personal_profile);
        //        debug($arr_empdetails);
    }

    public function getautocompletions_superior()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array('status' => "1");
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition[] = "EmployeeDetails.emp_pkey not in ('$branch' )";
            $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%'";
        } else {
            $searchkey = $arr_request_data['username'];
            //            $filter_condition[] = 'first_name LIKE "%' . $searchkey . '%"';
            $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%'";
        }

        //    debug($filter_condition);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,concat(first_name," ",ifnull(last_name," ")," ",EmployeeDetails.emp_id) as emp_name,branch_code', 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            //            array(
            //                'emp_pkey' => '',
            //                'emp_name' => 'ALL',
            //            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] =  array_merge(isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(), isset($val['0']) ? $val['0'] : array());
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function savepromotions()
    {

        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->Promotion->useDbConfig = $this->Session->read('ds');
        $this->Session->write('promotion_data', $arr_form_data);
        $promotion_data = $this->Session->read('promotion_data');
        // debug($promotion_data);
        $arr_save = array();
        $result = "";
        $arr_form_data['created_date'] = date('Y-m-d');
        $arr_form_data['promotion_status'] = 'APPLIED';
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');

        //Edited by Akshay on 17-10-2024
        $arr_form_data['start_date_effective'] = date('Y-m-d', strtotime($arr_form_data['start_date_effective'] . '-1'));
        //End
        //edited by athira on 21-02-2025
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $arr_form_data['emp_fkey'];
        $promo_data = $this->EmployeeProfessionalDetails->query("SELECT * FROM promotions where emp_type='Contract'");
        $promo_status = $promo_data['0']['promotions']['promotion_status'];
        $endDate = isset($arr_form_data['end_date']) ? $arr_form_data['end_date'] : null;
        $formatted_endDate = date('Y-m-d', strtotime($endDate));

        // if(!empty($emp_fkey)){
        //     if (!empty($formatted_endDate) && !empty($emp_fkey)) {
        //         $this->Promotion->query("UPDATE contracted_days SET end_date_effective = '$formatted_endDate' WHERE emp_fkey = '$emp_fkey'");

        //     }
        // }

        if (!empty($emp_fkey) && !empty($formatted_endDate)) {
            // Check if an entry exists for this employee in contracted_days
            $existingEntry = $this->Promotion->query("SELECT COUNT(*) as count FROM contracted_days WHERE emp_fkey = '$emp_fkey'");

            if (!empty($existingEntry) && $existingEntry[0][0]['count'] > 0) {
                // If entry exists, update the record
                $this->Promotion->query("UPDATE contracted_days SET end_date_effective = '$formatted_endDate' WHERE emp_fkey = '$emp_fkey'");
            } else {
                // If no entry exists, insert a new record
                $this->Promotion->query("INSERT INTO contracted_days (emp_fkey, end_date_effective) VALUES ('$emp_fkey', '$formatted_endDate')");
            }
        }
        //end

        $data['id'] = 0;
        $data['emp_fkey'] = $emp_fkey =  isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0; //Edited by Akshay on 18-9-2024
        $data['modified_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modification_date '] = date("Y-m-d H:i:s");


        $data['type'] = $this->Session->read('login_user_id');


        $save = $this->Promotion->save($arr_form_data);

        //Edited by Akshay on 18-9-2024
        $multishift =  isset($arr_form_data['multishift']) ? $arr_form_data['multishift'] : '';
        if ($multishift != '' && $emp_fkey != 0) {
            // $saveMultishift = $this->Promotion->query("UPDATE emp_proff ep SET ep.multishift = '$multishift' WHERE ep.emp_fkey = '$emp_fkey'");
            $arr_emp_config = $this->Promotion->query("SELECT id FROM emp_config WHERE type = 'MSHIFT' AND emp_fkey = '$emp_fkey' AND status = '1';");
            if (count($arr_emp_config) > 0) {
                $emp_config_id = isset($arr_emp_config[0]['emp_config']['id']) ? $arr_emp_config[0]['emp_config']['id'] : 0;
                if ($emp_config_id != 0) {
                    $saveMultishift = $this->Promotion->query("UPDATE emp_config SET policy_id = '$multishift' WHERE id = '$emp_config_id';");
                }
            } else {
                $data['id'] = 0;
                $data['type'] = 'MSHIFT';
                $data['emp_fkey'] = $emp_fkey;
                $data['policy_id'] = $multishift;
                $data['created_by'] = $this->Session->read('login_user_id');
                $this->EmployeeConfig->save($data);
            }
        }
        //End

        $message = "Employee Configuration Details Saved Successfully";
        return json_encode(array('success' => TRUE, "result" => $result, 'message' => $message));
    }

    public function approvepromotion($emp_fkey = 0)
    {


        $this->set("emp_pkey", $emp_fkey);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select * from promotions where status = 1 and approved_status = 'N' and emp_fkey = '' ");
        $this->set('arr_holidays', $arr_holidays);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        //Fetch Units for the company
        $arr_emp_structures = $this->EmployeeDetails->query("select * from employee_structure_vview where emp_pkey = $emp_fkey ");
        $this->set('arr_emp_structures', $arr_emp_structures);

        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_fkey and end_date_effective is null ");
        $this->set('arr_gross', $arr_gross);

        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_fkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $arr_empdetails = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_fkey)));
        $arr_emp_personal_profile = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
        foreach ($arr_personalinfokeys as $key) {
            $arr_emp_personal_profile[$key] = '';
        }

        $arr_professionalinfokeys = array();
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
        foreach ($arr_professionalinfokeys as $key) {
            $arr_emp_professional_profile[$key] = '';
        }
        $this->set('arr_personalinfo', $arr_emp_personal_profile);
        $this->set('arr_empdetails', $arr_empdetails);
        //        debug($arr_emp_personal_profile);
        //        debug($arr_empdetails);
    }

    public function empprofdetails($emp_pkey = 0)
    {
        $this->layout = null;
        $emp_pkey = 1;

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch employee professional details if in edit mode
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            $this->set('arr_emp_professional_profile', $arr_emp_professional_profile);
            $this->set('emp_id', $arr_emp_professional_profile['emp_id']);
            $emp_proff_pkey = $arr_emp_professional_profile['emp_proff_pkey'];
        } else {
            $emp_proff_pkey = 0;
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('last');
            if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                $company_key = $this->session->read('company_key');
                $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('company_pkey' => $company_key)));
                $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                $arr_emp_id = explode($str_company_code, $str_emp_id);
                if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                    $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                } else {
                    $emp_id = '';
                }
            } else {
                $emp_id = '';
            }
            $this->set('emp_id', $arr_emp_professional_profile_last['emp_id']);
        }
        $this->set('emp_proff_pkey', $emp_proff_pkey);
    }

    public function emptaxationdetails($emp_pkey = 0) {}

    public function uploadProfileImage($files)
    {
        // $target_dir = ASSETSPATH . "uploads/Registrations/";
        //$target_file = $target_dir . basename($files["avatarfile"]["name"]);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($files['avatarfile']['tmp_name']),
            array(
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf'
                //edited by megha on 6/6/19 removed xsl and zip
                // 'xlxs' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                // 'docx' => 'application/zip',
                //end
            ),
            true
        )) {
            $resp['error'] = 'Invalid file format.';
        }

        $filename = sprintf('img/avatar/%s.%s', sha1_file($files['avatarfile']['tmp_name']), $ext);

        $uploadOk = 1;
        $pic = "";
        $msg = "";

        // check if image is a
        $check = getimagesize($files["avatarfile"]["tmp_name"]);
        if ($check !== false) {
            $msg = "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            $msg = "Uploaded image is not an image.";
            $uploadOk = 0;
        }

        // check if the image size is too large
        if ($files["avatarfile"]["size"] > 500000) {
            $msg = "Sorry, your image is too large.";
            $uploadOk = 0;
        }
        // an actual image


        //$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        $file = $files["avatarfile"]['tmp_name'];
        list($width, $height) = getimagesize($file);

        // if($width != "450" || $height != "450") {
        //     $msg = "Error : Profile image size must be 450 x 450 pixels.";
        //     $uploadOk = 0;
        // }

        if ($uploadOk == 0) {
            // $msg = "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($files["avatarfile"]["tmp_name"], $filename)) {
                $pic = basename($files["avatarfile"]["name"]);
                $uploadOk = 1;
                $msg = "your file is uploaded.";
            } else {
                $msg = "Sorry, your image is not uploaded.";
                // return false;
                $uploadOk = 0;
            }
        }
        return array("success" => $uploadOk, "msg" => $msg, "image" => $filename);
    }

    public function saveToJoin()
    {
        $this->autoRender = false;
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
        $this->EmployeeJoin->primaryKey = 'emp_join_pkey';

        if ($this->request->is('post')) {
            $data = $this->request->data;
            // debug($data);

            // Normalize checkbox values
            $checkboxFields = ['eps', 'international_worker', 'physical_handicap', 'locomotive', 'hearing', 'visual'];
            foreach ($checkboxFields as $field) {
                $data[$field] = !empty($data[$field]) ? 'Y' : 'N';
            }
            // if ($data['international_worker'] == 'Y') {
            //     $data['country_origin'] = isset($data['country_origin_hidden']) ? $data['country_origin_hidden'] : null;
            // }
            if ($data['international_worker'] == 'Y') {
                $data['country_origin'] = !empty($data['country']) ? $data['country'] : null;
            }

            // Read posted values
            $id_card   = isset($data['id_card']) ? trim($data['id_card']) : '';
            $lwf_code  = isset($data['lwf_code']) ? trim($data['lwf_code']) : '';
            $pkey      = !empty($data['emp_pkey']) ? (int)$data['emp_pkey'] : 0;

            // Duplicate check for id_card
            //         if ($id_card !== '') {
            //             $arr_id_duplicate = $this->EmployeeJoin->query("
            //     SELECT ej.emp_join_pkey 
            //     FROM emp_join ej 
            //     WHERE ej.id_card = '" . addslashes($id_card) . "' 
            //       AND ej.status = 1
            //       AND ej.emp_join_pkey != {$pkey}
            // ");
            //             if (!empty($arr_id_duplicate)) {
            //                 echo json_encode([
            //                     'status'  => 'error',
            //                     'message' => 'Duplicate Aadhar number found.'
            //                 ]);
            //                 return;
            //             }
            //         }

            // Duplicate check for lwf_code
            //         if ($lwf_code !== '') {
            //             $arr_lwf_duplicate = $this->EmployeeJoin->query("
            //     SELECT ej.emp_join_pkey 
            //     FROM emp_join ej 
            //     WHERE ej.lwf_code = '" . addslashes($lwf_code) . "' 
            //       AND ej.status = 1
            //       AND ej.emp_join_pkey != {$pkey}
            // ");
            //             if (!empty($arr_lwf_duplicate)) {
            //                 echo json_encode([
            //                     'status'  => 'error',
            //                     'message' => 'Duplicate LWF code found.'
            //                 ]);
            //                 return;
            //             }
            //         }



            // Handle image upload
            // if (!empty($_FILES['profile_image']['tmp_name'])) {
            //     $file = $_FILES['profile_image'];
            //     $fileName = time() . '_' . basename($file['name']);
            //     $uploadPath = 'img/avatar/';
            //     $fullPath = $uploadPath . $fileName;

            //     if (!is_dir($uploadPath)) {
            //         mkdir($uploadPath, 0755, true);
            //     }

            //     if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            //         $data['profile_image_url'] = 'img/avatar/' . $fileName;
            //     } else {
            //         $data['profile_image_url'] = 'img/placeholdermen.jpeg';
            //     }
            // } else {
            //     if (!empty($data['emp_pkey'])) {
            //         $existing = $this->EmployeeJoin->find('first', [
            //             'conditions' => ['EmployeeJoin.emp_join_pkey' => $data['emp_pkey']],
            //             'fields' => ['profile_image_url']
            //         ]);
            //         $data['profile_image_url'] = isset($existing['EmployeeJoin']['profile_image_url']) ? $existing['EmployeeJoin']['profile_image_url'] : 'img/placeholdermen.jpeg';
            //     } else {
            //         $data['profile_image_url'] = 'img/placeholdermen.jpeg';
            //     }
            // }
            // Handle image delete first
            if (!empty($this->request->data['profile_image_delete']) && $this->request->data['profile_image_delete'] == '1') {
                // Delete from DB
                $data['profile_image_url'] = null;

                // Also delete file from server (if exists)
                if (!empty($data['emp_pkey'])) {
                    $existing = $this->EmployeeJoin->find('first', [
                        'conditions' => ['EmployeeJoin.emp_join_pkey' => $data['emp_pkey']],
                        'fields' => ['profile_image_url']
                    ]);

                    if (!empty($existing['EmployeeJoin']['profile_image_url'])) {
                        $filePath = 'img/avatar/' . $existing['EmployeeJoin']['profile_image_url'];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            } else {
                // Handle image upload
                if (!empty($_FILES['profile_image']['tmp_name'])) {
                    $file = $_FILES['profile_image'];
                    $fileName = time() . '_' . basename($file['name']);
                    $uploadPath = 'img/avatar/';
                    $relativePath = $uploadPath . $fileName;

                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $uploadPath . $fileName)) {
                        $data['profile_image_url'] = $relativePath;
                    } else {
                        $data['profile_image_url'] = 'img/placeholdermen.jpeg';
                    }
                } else {
                    // No upload — keep existing image or set placeholder
                    if (!empty($data['emp_pkey'])) {
                        $existing = $this->EmployeeJoin->find('first', [
                            'conditions' => ['EmployeeJoin.emp_join_pkey' => $data['emp_pkey']],
                            'fields' => ['profile_image_url']
                        ]);
                        $data['profile_image_url'] = !empty($existing['EmployeeJoin']['profile_image_url'])
                            ? $existing['EmployeeJoin']['profile_image_url']
                            : 'img/placeholdermen.jpeg';
                    } else {
                        $data['profile_image_url'] = 'img/placeholdermen.jpeg';
                    }
                }
            }



            $data['status'] = 1;


            // ✅ Ensure emp_join_pkey is passed for update
            if (!empty($data['emp_pkey'])) {
                $this->EmployeeJoin->id = $data['emp_pkey'];
                $existing = $this->EmployeeJoin->findByEmpJoinPkey($data['emp_pkey']);


                if (!$existing) {
                    echo json_encode(['status' => 'error', 'message' => 'Record not found for update.']);
                    return;
                }
            } else {
                $this->EmployeeJoin->create(); // insert new
            }

            // if ($this->EmployeeJoin->save($data)) {
            //     echo json_encode(['status' => 'success', 'message' => 'Employee data saved successfully.']);
            // } else {
            //     echo json_encode(['status' => 'error', 'message' => 'Failed to save employee data.']);
            // }
            // debug($data);
            if ($this->EmployeeJoin->save($data)) {

                $empJoinPkey = $this->EmployeeJoin->id;

                echo json_encode([
                    'status'        => 'success',
                    'message'       => 'Employee data saved successfully.',
                    'emp_join_pkey' => $empJoinPkey
                ]);
            } else {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Failed to save employee data.'
                ]);
            }
        }
    }


    public function saveExperience()
    {
        $this->autoRender = false;

        $this->WorkExperience->useDbConfig = $this->Session->read('ds');

        $data = $this->request->data;
        if (empty($data)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data received.'
            ]);
            return;
        }

        // --- Check for duplicate record ---
        // $empId = $data['emp_join_fkey'];
        // $fromDate = $data['from_date'];
        // $toDate = $data['to_date'];

        // // Check if from_date already exists
        // $existingFrom = $this->WorkExperience->find('first', [
        //     'conditions' => [
        //         'emp_join_fkey' => $empId,
        //         'from_date' => $fromDate
        //     ]
        // ]);

        // if ($existingFrom) {
        //     echo json_encode([
        //         'status' => 'error',
        //         'message' => 'Duplicate record found. The from_date already exists for this employee.'
        //     ]);
        //     return;
        // }

        // // Check if to_date already exists
        // $existingTo = $this->WorkExperience->find('first', [
        //     'conditions' => [
        //         'emp_join_fkey' => $empId,
        //         'to_date' => $toDate
        //     ]
        // ]);

        // if ($existingTo) {
        //     echo json_encode([
        //         'status' => 'error',
        //         'message' => 'Duplicate record found. The to_date already exists for this employee.'
        //     ]);
        //     return;
        // }

if (empty($data['emp_join_fkey'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Please fill personal details.'
        ]);
        return;
    }

        if (!empty($data)) {
            $this->WorkExperience->create(); // Always create new record
            $saveData = [
                'emp_join_fkey' => $data['emp_join_fkey'],
                'company'       => $data['company'],
                'department'    => $data['department'],
                'designation'   => $data['designation'],
                'from_date'     => $data['from_date'],
                'to_date'       => $data['to_date'],
                'salary'        => $data['salary'],
                'status'        => 1
            ];

            if ($this->WorkExperience->save($saveData)) {
                $empJoinPkey = $this->EmployeeJoin->id;
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Experience details saved successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save Experience details.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data received.'
            ]);
        }
    }
    public function saveEducation()
    {
        $this->autoRender = false;
        $this->Education->useDbConfig = $this->Session->read('ds');

        $data = $this->request->data;

        if (empty($data)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data received.'
            ]);
            return;
        }

        // // --- Check for duplicate record ---
        // $existing = $this->Education->find('first', [
        //     'conditions' => [
        //         'emp_join_fkey' => $data['emp_join_fkey'],
        //         'duration' => $data['duration']
        //     ]
        // ]);

        // if ($existing) {
        //     echo json_encode([
        //         'status' => 'error',
        //         'message' => 'Duplicate record found. This duration already exists for this employee.'
        //     ]);
        //     return;
        // }
if (empty($data['emp_join_fkey'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Please fill personal details.'
        ]);
        return;
    }
        // --- Save new education record ---
        $this->Education->create();
        $saveData = [
            'emp_join_fkey' => $data['emp_join_fkey'],
            'course'        => $data['course'],
            'university'    => $data['university'],
            'duration'      => $data['duration'],
            'mark'          => $data['mark']
        ];

        if ($this->Education->save($saveData)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Education details saved successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save education details.'
            ]);
        }
    }


    // public function saveEducation()
    // {
    //     $this->autoRender = false;

    //     $this->Education->useDbConfig = $this->Session->read('ds');

    //     $data = $this->request->data;

    //     if (!empty($data)) {
    //         $this->Education->create(); // Always create new record

    //         $saveData = [
    //             'emp_join_fkey'   => $data['emp_join_fkey'],
    //             'course'     => $data['course'],
    //             'university' => $data['university'],
    //             'duration'   => $data['duration'],
    //             'mark'       => $data['mark']
    //         ];

    //         if ($this->Education->save($saveData)) {
    //             echo json_encode([
    //                 'status' => 'success',
    //                 'message' => 'Education details saved successfully.'
    //             ]);
    //         } else {
    //             echo json_encode([
    //                 'status' => 'error',
    //                 'message' => 'Failed to save education details.'
    //             ]);
    //         }
    //     } else {
    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => 'No data received.'
    //         ]);
    //     }
    // }

    public function saveFam()
    {
        $this->autoRender = false;

        $this->EmpFam->useDbConfig = $this->Session->read('ds');

        $data = $this->request->data;
        $data['created_by'] = $this->Session->read('login_user_id');
if (empty($data['emp_join_fkey'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Please fill personal details.'
        ]);
        return;
    }
        if (!empty($data)) {
            $this->EmpFam->create(); // Always create new record

            $saveData = [
                'emp_join_fkey'     => $data['emp_join_fkey'],
                'name'              => $data['name'],
                'relation'          => $data['relation'],
                'DOB'               => $data['DOB'],
                'blood_group'       => $data['blood_group'],
                'gender'            => $data['gender'],
                'nationality'       => $data['nationality'],
                'contact_number'    => $data['contact_number'],
                'alternate_number'  => $data['alternate_number'],
                'emergency_contact' => $data['emergency_contact'], // default
                'is_nominee'        => $data['is_nominee'], // default
                'created_by'        => $data['created_by'],
                'created_date'      => date('Y-m-d H:i:s'),
                'status'            => 1
            ];

            if ($this->EmpFam->save($saveData)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Family details saved successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save family details.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data received.'
            ]);
        }
    }

    public function saveDoc()
    {
        $this->autoRender = false;

        $this->EmpDocument->useDbConfig = $this->Session->read('ds');

        $data = $this->request->data;
        if (empty($data['emp_join_fkey'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Please fill personal details.'
        ]);
        return;
    }

        if (!empty($data)) {

            $filePath = null;
            if (!empty($_FILES['avatarfile']['name'])) {
                $uploadDir = 'img/avatar/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                // generate unique filename based on sha1_file
                $ext = strtolower(pathinfo($_FILES['avatarfile']['name'], PATHINFO_EXTENSION));
                $newFileName = sha1_file($_FILES['avatarfile']['tmp_name']) . '.' . $ext;
                $fullPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['avatarfile']['tmp_name'], $fullPath)) {
                    // save relative path (for DB)
                    $filePath = 'img/avatar/' . $newFileName;
                }
            }



            $this->EmpDocument->create(); // Always create new record

            $saveData = [
                'emp_join_fkey'   => $data['emp_join_fkey'],
                'name'     => $data['name'],
                'document_type' => $data['document_type'],
                'document_number'   => $data['document_number'],
                'relation' => $data['relation'],
                'valid_from' => $data['valid_from'],
                'valid_till' => $data['valid_till'],
                'classification' => $data['classification'],
                'nationality' => $data['nationality'],
                'files' => $filePath,
                'remind' => $data['remind'],
                'reccuring' => $data['reccuring'],
                'status'       => 1
            ];

            if ($this->EmpDocument->save($saveData)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Document details saved successfully.',
                    'file_path' => $filePath
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save document details.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data received.'
            ]);
        }
    }

    // public function restsave($emp_pkey = 0)
    // {
    //     $this->UserCredentials->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
    //     $company_code = $this->Session->read('company_code');
    //     try {
    //         $this->EmployeeProfessionalDetails->query("delete from emp_details where emp_pkey = $emp_pkey ");
    //         $this->EmployeeProfessionalDetails->query("delete from emp_proff where emp_fkey = $emp_pkey ");
    //         $this->EmployeeProfessionalDetails->query("delete from user_credentials where emp_fkey = $emp_pkey ");
    //         $this->EmployeeProfessionalDetails->query("delete from mypayrol_control_db.emp_device_comp_branch where emp_fkey = $emp_pkey and Company_code = '$company_code'");
    //         return True;
    //     } catch (Exception $ex) {
    //         return False;
    //     }
    // }

    public function saveemployeesetup()
    {
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $model = $arr_form_data['model'];
        switch ($model) {
            case 'EmployeeDetails':
                $pkey = $arr_form_data['emp_pkey'];
                $arr_form_data['company_code'] = $this->Session->read('company_code');
                $message = 'Personal Details Saved Successfully';
                //$empname = $arr_form_data['first_name'] .' '.  $arr_form_data['last_name'];
                //$this->mailsend($empname);

                break;
            case 'EmployeeProfessionalDetails':
                $pkey = $arr_form_data['emp_fkey'];
                $message = 'Professional Details Saved Successfully';
                $increment = $arr_form_data['attr2'];
                $date = strtotime("+$increment day", strtotime($arr_form_data['joining_date']));
                $arr_form_data['attr3'] = date("Y-m-d", $date);

                break;
            default:
                $pkey = 0;
                $message = '';
                break;
        }
        $this->{$model}->useDbConfig = $this->Session->read('ds');
        $result = $this->{$model}->save($arr_form_data);
        if (!empty($result)) {
            if ($pkey == 0) {
                $pkey = $this->{$model}->getLastInsertID();
                if ($model == 'EmployeeDetails') {
                    //$this->sendpasswordemail($pkey);
                    //$sessionObj = $this->Session->read("Auth.User");
                    //$company_key    =   $sessionObj['company_key'];
                    $company_key = $this->Session->read('company_key');
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                    $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                    if ($punch_type == 'device') {
                        //Device available, so generate emp id concatenate with device id and emp id from device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials  
                            //Get company_code
                            $str_company_code = $this->Session->read('company_code');

                            $emp_username = ''; //No device details here on manually entering emp data

                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = $emp_username;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
                        }
                    } else {
                        //Manually generate emp id for companies without device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials  
                            $str_company_code = $this->Session->read('company_code');
                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
                        }
                    }
                }
            } else {
                if ($model == 'EmployeeDetails') {
                    //Update user credentials
                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                    $this->UserCredentials->updateAll(
                        array(
                            'UserCredentials.first_name' => "'" . $arr_form_data['first_name'] . "'",
                            'UserCredentials.last_name' => "'" . $arr_form_data['last_name'] . "'",
                            'UserCredentials.middle_name' => "'" . $arr_form_data['middile_name'] . "'",
                            'UserCredentials.email' => "'" . $arr_form_data['email'] . "'",
                            'UserCredentials.phone' => "'" . $arr_form_data['mobile_no'] . "'",
                        ),
                        array('UserCredentials.emp_fkey' => $pkey)
                    );
                }
            }

            if (isset($arr_form_data['emp_proff_pkey']) && ($arr_form_data['emp_proff_pkey'] == 0 || $arr_form_data['emp_proff_pkey'] == '')) {
                //Call procedure 'Linkemp_deviceanddatabase'
                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                $outputParameter = array();
                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'" . $arr_form_data['emp_branch'] . "'" : "''";
                $outputParameter[] = "''";
                $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
            }


            //$this->autoRender = FALSE;





            return json_encode(array('success' => TRUE, 'pkey' => $pkey, 'message' => $message));
        }
    }

    public function addToNotice($shift_id = 0, $emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition3['type'] = 'NOTICEPER';
        $condition3['emp_fkey'] = $emp_pkey;
        //$condition3['policy_id'] = $arr_form_data['leave'];
        $data['id'] = 0;
        $data['emp_fkey'] = $emp_pkey;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        //$data['creation_date'] = date("Y-m-d");

        try {
            $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $this->Session->read('login_user_id') . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
            $data['type'] = 'NOTICEPER';
            $data['policy_id'] = $shift_id;
            $this->EmployeeConfig->saveAll($data);
            return true;
        } catch (Exception $ex) {
            return json_encode(array('success' => FALSE, "result" => "", 'message' => "Failed to Save leave policy "));
        }
    }

    public function jsons_getemps($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = "and branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        if ($q != null) {
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $emp_condition = "and emp_proff.attr1 = '$emp_pkeys' ";
        } else {
            $emp_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1 $branch_condition $q_condition $emp_condition");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function saveconfigs()
    {

        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $edit_salary = FALSE;
        $upload_salary = FALSE;
        $result = "";
        $gratuity = isset($arr_form_data['gratuity']) ? $arr_form_data['gratuity'] : '';
        if ($gratuity == '') {
            $gratuity = 0;
        } else {
            $gratuity = $gratuity;
        }
        $emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;


        //edited by sinisya 13-04-2024
        $fetch_annualgross = $this->EmployeeConfig->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = '$emp_fkey' and end_date_effective is null ");

        $annualgross = isset($fetch_annualgross['0']['emp_ctc_transaction']['emp_anual_ctc']) ? $fetch_annualgross['0']['emp_ctc_transaction']['emp_anual_ctc'] : 0;

        try {
            $this->EmployeeConfig->query("update emp_proff set emp_mgt_priv = $gratuity where emp_fkey=$emp_fkey ");
            $fetch_proff = $this->EmployeeConfig->query("select * from emp_proff where emp_fkey = '$emp_fkey' ");
        } catch (Exception $ex) {
            return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to get proffessional Details "));
        }

        try {
            $arr_gross = $this->EmployeeConfig->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = '$emp_fkey' and end_date_effective is null ");
        } catch (Exception $ex) {

            return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to get annual ctc "));
        }


        $shift_id = isset($fetch_proff['0']['emp_proff']['day_time_seq']) ? $fetch_proff['0']['emp_proff']['day_time_seq'] : 0;
        $leave_policy = isset($fetch_proff['0']['emp_proff']['LEAVEPOLICY_GROUP_ID']) ? $fetch_proff['0']['emp_proff']['LEAVEPOLICY_GROUP_ID'] : 0;
        $holiday_id = isset($fetch_proff['0']['emp_proff']['HOLIDAY_GROUP_ID']) ? $fetch_proff['0']['emp_proff']['HOLIDAY_GROUP_ID'] : 0;
        $salary_id = isset($fetch_proff['0']['emp_proff']['structure_id']) ? $fetch_proff['0']['emp_proff']['structure_id'] : 0;
        $hierarchy = isset($fetch_proff['0']['emp_proff']['attr1']) ? $fetch_proff['0']['emp_proff']['attr1'] : 0;

        $data['id'] = 0;
        $data['emp_fkey'] = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modified_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modification_date '] = date("Y-m-d H:i:s");


        $data['type'] = $this->Session->read('login_user_id');


        if (isset($arr_form_data['shift']) && $arr_form_data['shift'] != $shift_id) {


            $condition['type'] = 'SHIFT';
            $condition['emp_fkey'] = $arr_form_data['emp_fkey'];

            $data['type'] = 'SHIFT';

            $data['policy_id'] = $arr_form_data['shift'];
            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);

                $this->EmployeeConfig->save($data);
            
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save shift policy "));
            }
        }


        if (isset($arr_form_data['holidays']) && $arr_form_data['holidays'] != $holiday_id) {

            $condition1['type'] = 'HOLIDAY';
            $condition1['emp_fkey'] = $arr_form_data['emp_fkey'];

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition1);

                $data['type'] = 'HOLIDAY';
                $data['policy_id'] = $arr_form_data['holidays'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save holiday policy "));
            }
        }
        if (isset($arr_form_data['salary']) && $arr_form_data['salary'] != $salary_id) {

            $condition2['type'] = 'SALARY';
            $condition2['emp_fkey'] = $arr_form_data['emp_fkey'];

            $edit_salary = TRUE;

            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $salary_id = $arr_form_data['salary'];
            $emp = $arr_form_data['emp_fkey'];
            //added by megha salary allocation 1
            $resp = 0;
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $user_ids = $this->Session->read('login_user_id');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            try {
                $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                //debug($proc);exit;
                //employee condition added by megha on 5/03/2020
                $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                    "all",
                    array(
                        'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                        'conditions' => array(
                            'emp_structure_id' => $salary_id,
                            'remarks IS NOT NULL',
                            'emp_fkey' => $emp,
                            'end_date_effective is null'
                        )
                    )
                );

                foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                    $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                    $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                    $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';

                    $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';
                    // $head_type=isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_type'])?$row_formulae_from_remarks['EmpSalarySlip']['head_type']:'';
                    if (!empty($formula_from_remarks)) {
                        eval('$salary_amount = ' . $formula_from_remarks . ';');
                        // debug($salary_amount);
                        //edited by sinsiya on 26-09-2024
                        //                          if($head_operator == 'Deduction'){
                        //                                  $salary_amount = ceil($salary_amount); //Edited by Akshay on 30-4-2024
                        //                                }else{
                        //                                 $salary_amount = round($salary_amount); //Edited by Akshay on 30-4-2024
                        //                                 }
                        //end
                        if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                            if ($head_operator == 'Deduction') {
                                $salary_amount = ceil($salary_amount); //Edited by Akshay on 30-4-2024
                            } else {
                                $salary_amount = round($salary_amount); //Edited by Akshay on 30-4-2024
                            }


                            // if (trim(strtolower($salary_head_item_desc)) =='esi' || trim(strtolower($salary_head_item_desc)) =='esi - employee contribution' || trim(strtolower($salary_head_item_desc)) =='esi - employer contribution'){         
                            if ($head_operator == 'Deduction') {
                                $salary_amount *= -1;
                            }
                            // }
                            //                         if (trim(strtolower($salary_head_item_desc)) == 'epf - employee contribution'){         
                            //                            if ($head_operator == 'Deduction') {
                            //                                  $salary_amount *= -1;
                            //                             }
                            //                         }

                            $arr_emp_salary_slip_data = array(
                                'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                            );
                        } else {
                            $salary_amount = round($salary_amount);
                            if ($head_operator == 'Deduction') {
                                $salary_amount *= -1;
                            }
                            $arr_emp_salary_slip_data = array(
                                'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                            );
                        }
                        //  debug($arr_emp_salary_slip_data);
                        $this->EmployeeSalaryStructure->updateAll(
                            $arr_emp_salary_slip_data,
                            array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                        );
                    }
                }
                // debug($salary_amount);exit;

                $resp = 1;
            } catch (Exception $ex) {
                $resp = 0;
            }
            //end salary allocation
            try {
                $user_ids = $this->Session->read('login_user_id');

                //debug($result);
                $data['type'] = 'SALARY';
                $data['policy_id'] = $arr_form_data['salary'];
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition2);

                $this->EmployeeConfig->saveAll($data);
                //added by megha sallary allocation 2
                $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save salary policy "));
            }
        }


        if (isset($arr_form_data['leave']) && $arr_form_data['leave'] != $leave_policy) {

            $condition3['type'] = 'LEAVE';
            $condition3['emp_fkey'] = $arr_form_data['emp_fkey'];
            //$condition3['policy_id'] = $arr_form_data['leave'];

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
                $data['type'] = 'LEAVE';
                $data['policy_id'] = $arr_form_data['leave'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save leave policy "));
            }
        }


        if (isset($arr_form_data['hierarch']) && $arr_form_data['hierarch'] != $hierarchy) {

            $condition4s['type'] = 'HIERARCHY';
            $condition4s['emp_fkey'] = $arr_form_data['emp_fkey'];
            //$condition4s['policy_id'] = $arr_form_data['hierarch'];

            $data['type'] = 'HIERARCHY';

            try {
                $this->EmployeeConfig->updateAll(
                    array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0),
                    $condition4s
                );

                $data['policy_id'] = $arr_form_data['hierarch'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save hierarchy policy "));
            }
        }


        if (isset($arr_form_data['annual_gross']) && $arr_form_data['annual_gross'] != '') {

            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');


            $upload_salary = TRUE;

            $arr_form_data['emp_anual_ctc'] = $arr_form_data['annual_gross'];


            // try {
            //edited by sinsiya 13-04-2024
            if (isset($annualgross) && $annualgross != $arr_form_data['annual_gross']) {
                $arr_form_data['start_date_effective'] = date("Y-m-1");
                $result = $this->EmployeeCTC->save($arr_form_data);
            }
            // } catch (Exception $ex) {
            //     return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Upload Salary "));
            // }
        }


        if ($edit_salary == TRUE && $upload_salary == FALSE) {
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');
            $salary_id = $arr_form_data['salary'];
            $user_ids = $this->Session->read('login_user_id');
            $emp = $arr_form_data['emp_fkey'];
            try {
                $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                $result = isset($proc['0']['0']['function']) ? $proc['0']['0']['function'] : '';
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Create Salary "));
            }
        }

        $message = "Employee Configuration Details Saved Successfully";
        return json_encode(array('success' => TRUE, "result" => $result, 'message' => $message));
    }

    public function downloadempdataformat()
    {
        $this->autoRender = FALSE;

        //$auth_user  =   $this->Session->read("Auth.User");
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? $str_company_code . ".xlsx" : "employeedataformat_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        // create a file pointer connected to the output stream
        //$output = fopen('php://output', 'w');

        App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empcsvdata = new EmployeeCSVData();
        $emp_details_schema = $empcsvdata->getFieldNames('NewEmployeeDetails'); // get field-name=>label
        //edited by athira on 18-07-2025
        if (in_array($str_company_code, ['DEMO', 'GLET'])) {
            if (isset($emp_details_schema['id_card'])) {
                $emp_details_schema['id_card'] = 'Aadhaar No';
            }
            if (isset($emp_details_schema['company_pf'])) {
                $emp_details_schema['company_pf'] = 'PF No';
            }
        }
        //end
        $emp_prof_schema = $empcsvdata->getFieldHeadings('NewEmployeeProfessionalDetails');

        $emp_schema = array_merge(array_values($emp_details_schema), $emp_prof_schema);

        $objPHPExcel = new PHPExcel();
        $objWorkSheet = $objPHPExcel->createSheet(2);

        $objWorkSheet->getStyle('H4')->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        //$objWorkSheet->getStyle('C3')->getAlignment()->setWrapText(true);
        $cll = 3;
        $clld = 3;
        $clldd = 3;
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $leaves = $this->FinancialYear->query("select * from designation where status=1");
        $leavesdept = $this->FinancialYear->query("select * from department where status=1");
        $leavesgrade = $this->FinancialYear->query("select * from grade where status=1");
        //Setting Up Bold cell
        $objWorkSheet->getStyle('A1')->getFont()->setBold(true);

        $objWorkSheet->getStyle('A2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('A20')->getFont()->setBold(true);
        $objWorkSheet->getStyle('B2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('C2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('D2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('E2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('F2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('G2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('H2')->getFont()->setBold(true);

        //$objWorkSheet->getStyle('H3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('I3')->getFont()->setBold(true);

        $objWorkSheet->getStyle('J2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('K2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('I2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('I3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('J3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('K3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('L3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('M2')->getFont()->setBold(true); //Grade
        $objWorkSheet->getStyle('M3')->getFont()->setBold(true); //Grade Code
        $objWorkSheet->getStyle('N3')->getFont()->setBold(true); //Grade Name
        //Setting the width for all cells
        $objWorkSheet->getColumnDimension('B')->setWidth(20);
        $objWorkSheet->getColumnDimension('D')->setWidth(10);
        $objWorkSheet->getColumnDimension('E')->setWidth(24);
        $objWorkSheet->getColumnDimension('F')->setWidth(24);
        $objWorkSheet->getColumnDimension('G')->setWidth(20);
        $objWorkSheet->getColumnDimension('H')->setWidth(20);
        $objWorkSheet->getColumnDimension('I')->setWidth(20);
        $objWorkSheet->getColumnDimension('J')->setWidth(20);
        $objWorkSheet->getColumnDimension('K')->setWidth(20);
        $objWorkSheet->getColumnDimension('L')->setWidth(20);
        $objWorkSheet->getColumnDimension('M')->setWidth(20);
        $objWorkSheet->getColumnDimension('N')->setWidth(20);

        //Rendering Designation , Department Cell
        foreach ($leaves as $lev) {
            $cll = $cll + 1;
            $objWorkSheet->setCellValueExplicit('I' . $cll, $lev['designation']['desig_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('J' . $cll, $lev['designation']['desig_name']);
        }
        foreach ($leavesdept as $lev) {
            $clld = $clld + 1;
            $objWorkSheet->setCellValueExplicit('K' . $clld, $lev['department']['dept_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('L' . $clld, $lev['department']['dept_name']);
        }
        foreach ($leavesgrade as $lev) {
            $clldd = $clldd + 1;
            $objWorkSheet->setCellValueExplicit('M' . $clldd, $lev['grade']['grade_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('N' . $clldd, $lev['grade']['grade_name']);
        }
        $rowindexhelp = 2;
        $objWorkSheet->setCellValue('A1', 'Help Form');
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $objWorkSheet->getStyle('I2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->getStyle('K2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->getStyle('M2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->mergeCells("I2:J2");
        $objWorkSheet->mergeCells("K2:L2");
        $objWorkSheet->mergeCells("M2:N2");
        $objWorkSheet->mergeCells("A10:F10");
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objWorkSheet->setCellValue('A2', 'First Name , Last Name');
        $objWorkSheet->setCellValue('A3', 'First Name and Last Name');
        $objWorkSheet->setCellValue('B2', 'Gender');
        $objWorkSheet->setCellValue('B3', 'Male');
        $objWorkSheet->setCellValue('B4', 'Female');
        $objWorkSheet->setCellValue('C2', 'Email');
        $objWorkSheet->setCellValue('C3', 'Email id should be in the correct format');
        $objWorkSheet->setCellValue('D2', 'Marital Status');
        $objWorkSheet->setCellValue('D3', 'Single');
        $objWorkSheet->setCellValue('D4', 'Married');
        $objWorkSheet->setCellValue('E2', 'Date of Birth , Joining Date');
        $objWorkSheet->setCellValue('E3', 'Should be in the format of (DD-MM-YYYY)');
        $objWorkSheet->setCellValue('F2', 'Company Employee ID');
        $objWorkSheet->setCellValue('F3', 'Leave If not Have ');
        $objWorkSheet->setCellValue('G2', 'Employee Type');
        $objWorkSheet->setCellValue('G3', 'Permanent');
        $objWorkSheet->setCellValue('G4', 'Probation');
        $objWorkSheet->setCellValue('G5', 'Contract');
        $objWorkSheet->setCellValue('G6', 'Part Time');
        $objWorkSheet->setCellValue('G7', 'Temporary');
        $objWorkSheet->setCellValue('G8', 'Other');
        //        $objWorkSheet->setCellValue('B2', 'Last Name');$objWorkSheet->setCellValue('A1', 'Terms');
        $objWorkSheet->setCellValue('H2', 'ID/AADHAR Card Number');
        $objWorkSheet->setCellValue('H3', 'ID/AADHAR Card Number');

        $objWorkSheet->setCellValue('I2', 'Designation');
        $objWorkSheet->setCellValue('I3', 'Designation Code');
        $objWorkSheet->setCellValue('J3', 'Designation Name');
        $objWorkSheet->setCellValue('K2', 'Department');
        $objWorkSheet->setCellValue('K3', 'Department Code');
        $objWorkSheet->setCellValue('L3', 'Department Name');
        $objWorkSheet->setCellValue('M2', 'Grade');
        $objWorkSheet->setCellValue('M3', 'Grade Code');
        $objWorkSheet->setCellValue('N3', 'Grade Name');
        $objWorkSheet->setCellValue('A20', 'Mandatory Fields :');
        $objWorkSheet->setCellValue('A21', 'First Name, Last Name, Gender, Date of Birth, Joining Date, ID/AADHAR Card Number, Employee Type, Designation, Department');
        //$objWorkSheet->setCellValueExplicit('K1', '0022',PHPExcel_Cell_DataType::TYPE_STRING); To set a cell value as String with leading zero 
        //        $objWorkSheet->setCellValue('B2', 'First Half = 1');
        //        $objWorkSheet->setCellValue('B3', 'Second Half = 2');
        $objWorkSheet->setTitle('Help');
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();

        $Desig_type = array();

        $this->Designation->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->Designation->find("all", array("conditions" => array("status" => 1)));

        foreach ($arr_leavetypes as $arr_leavetypes) {
            $Desig_type[] = $arr_leavetypes['Designation']['desig_code'];
        }
        $leavetype = implode(", ", $Desig_type);

        //Ends

        $objActiveSheet = $objPHPExcel->getActiveSheet();
        $objActiveSheet->getStyle('A1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('B1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('C1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('D1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('E1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('F1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('G1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('H1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('I1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('J1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('K1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('L1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('M1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('N1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('O1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('P1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Q1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('R1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('S1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('T1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('U1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('V1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('W1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('X1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Y1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Z1')->getFont()->setBold(true);
        //added by megha on 1_06_19 column heading bold
        $objActiveSheet->getStyle('AA1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('AB1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('AC1')->getFont()->setBold(true);
        //added by megha on 1_06_19 column heading bold
        $objActiveSheet->getStyle('AD1')->getFont()->setBold(true); //Grade Added by ***ARUL P DAS on 31/1/2020

        $objActiveSheet->getColumnDimension('A')->setWidth(20);
        $objActiveSheet->getColumnDimension('B')->setWidth(10);
        $objActiveSheet->getColumnDimension('C')->setWidth(20);
        $objActiveSheet->getColumnDimension('D')->setWidth(10);
        $objActiveSheet->getColumnDimension('E')->setWidth(10);
        $objActiveSheet->getColumnDimension('F')->setWidth(10);
        $objActiveSheet->getColumnDimension('G')->setWidth(10);
        $objActiveSheet->getColumnDimension('H')->setWidth(10);
        $objActiveSheet->getColumnDimension('I')->setWidth(10);
        $objActiveSheet->getColumnDimension('J')->setWidth(10);
        $objActiveSheet->getColumnDimension('K')->setWidth(10);
        $objActiveSheet->getColumnDimension('L')->setWidth(10);
        $objActiveSheet->getColumnDimension('M')->setWidth(16);
        $objActiveSheet->getColumnDimension('N')->setWidth(10);
        $objActiveSheet->getColumnDimension('O')->setWidth(20);
        $objActiveSheet->getColumnDimension('P')->setWidth(20);
        $objActiveSheet->getColumnDimension('Q')->setWidth(20);
        $objActiveSheet->getColumnDimension('R')->setWidth(20);
        $objActiveSheet->getColumnDimension('S')->setWidth(20);
        $objActiveSheet->getColumnDimension('T')->setWidth(20);
        $objActiveSheet->getColumnDimension('U')->setWidth(20);
        $objActiveSheet->getColumnDimension('V')->setWidth(20);
        $objActiveSheet->getColumnDimension('X')->setWidth(20);
        $objActiveSheet->getColumnDimension('Y')->setWidth(20);
        $objActiveSheet->getColumnDimension('Z')->setWidth(30);
        //added by megha on 1_06_19 column heading bold & set width
        $objActiveSheet->getColumnDimension('AA')->setWidth(20);
        $objActiveSheet->getColumnDimension('AB')->setWidth(20);
        $objActiveSheet->getColumnDimension('AC')->setWidth(20);
        //added by megha on 1_06_19 column heading bold & set width
        $objActiveSheet->getColumnDimension('AD')->setWidth(20); //Grade added by ***ARUL P DAS on 31/1/2020

        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                //$objPHPExcel->getActiveSheet()->setWidth(10);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee Data');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function mailsend($empname = '')
    {
        //$database = $this->Session->read('ds');
        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailerAutoload.php'));
        $mail = new PHPMailer;
        $mail->SMTPDebug = 2;                               // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
        $mail->Password = 'welcome123';                           // SMTP password
        $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
        $mail->addAddress('sanjundev@gmail.com', 'Sanjun Dev');     // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = '' . $database . ' Added a new Employee';
        $mail->Body = 'This is the HTML message body <b> Name:' . $empname . ' </b>';
        //$mail->Subject  = '<hr><h1><strong>HI ! '.$empname.' </strong></h1>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if (!$mail->send()) {
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            //echo 'Message has been sent';
        }
    }

    public function uploadandsaveempdetails($emp_branch = 0)
    {
        $this->autoRender = FALSE;
        //$authuser   =   $this->Session->read("Auth.User");
        $authuser['company_code'] = $this->Session->read('company_code');
        $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_' . strtotime("now") . '.xlsx' : 'empdata_' . strtotime("now") . '.xlsx';
        $targetpath = getcwd() . "/files/" . $filename;
        $errors = array();
        $duplicate_id = array(); //Edited by Akshay on 29-11-2023
        $message = 'Employee data imported successfully ';
        $intImportedCount = 0;

        //Edited by Athira on 28-12-2024
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // Fetching the maximum employee count from the comp_contact_info table
        // $emp_max_count = $this->EmployeeDetails->query("SELECT max_emp_count FROM comp_contact_info");
        // $emp_max_count = $emp_max_count[0]['comp_contact_info']['max_emp_count']; // Correcting the result structure
        // $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
        // $maxCount = (int)$emp_max_count;
        // $allowedCount = $maxCount - $active_emp_count;


        if (move_uploaded_file($_FILES['empdata']['tmp_name'][0], $targetpath)) {

            App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

            $objReader = new PHPExcel_Reader_Excel2007();
            $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

            $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
            $lastColumn++;
            $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $arrayempdata = array();

            $mandatory_fields_warning = FALSE;


            if ($highestRowIndex > 1) {
                $arrDuplicateEmpList = array();
                //atleast one employee records found
                $index = 0;
                for ($row = 1; $row <= $highestRowIndex; $row++) {
                    if ($row == 1) {
                        //Get mandatory headings array here
                        $array_mandatory_columns = array();
                        $array_mandatory_column_names = array('First Name', 'Last Name', 'Gender(Male/Female)', 'Date of Birth (DD-MM-YYYY)', 'ID/AADHAR Card Number', 'Joining Date (DD-MM-YYYY)', 'Employee Type', 'Designation CODE', 'Department CODE');
                        for ($col = 'A'; $col != $lastColumn; $col++) {
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                            if (in_array($value, $array_mandatory_column_names)) {
                                array_push($array_mandatory_columns, $col);
                            } else {
                                // debug($value);
                            }
                        }
                    } else {
                        for ($col = 'A'; $col != $lastColumn; $col++) {

                            if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                if ($objPHPExcel->getActiveSheet()->getCell("A" . $row)->getValue() != '') {
                                    $mandatory_fields_warning = true;
                                    $fieldpath = $col . ":" . $row;
                                    //debug($col.":".$row);
                                    break 2;
                                }
                            }
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                            $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                        }
                        $index++;
                    }
                    // $intImportedCount++;  
                }

                if ($mandatory_fields_warning) {
                    //Exit if mandatory fields not entered
                    unlink($targetpath);
                    echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields are entered. Data missing in ' . $fieldpath . ' cell'));
                    exit;
                } else {
                    //Continue with save if mandatory field warning is not there
                    //Save employees and return success
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                    $this->Designation->useDbConfig = $this->Session->read('ds');
                    $this->Departments->useDbConfig = $this->Session->read('ds');
                    $this->Grades->useDbConfig = $this->Session->read('ds');
                    $arr_designations = Set::extract('/Designation/.', $this->Designation->find("all", array("fields" => array("desig_code"), "Conditions" => array("status" => 1))));
                    $desigantions = array();
                    foreach ($arr_designations as $vals) {
                        $desigantions[] = trim($vals['desig_code']); //Edited by Akshay on 30-11-2023
                    }
                    $arr_departments = Set::extract('/Departments/.', $this->Departments->find("all", array("fields" => array("dept_code"), "Conditions" => array("status" => 1))));
                    $departments = array();
                    foreach ($arr_departments as $vals) {
                        $departments[] = trim($vals['dept_code']); //Edited by Akshay on 30-11-2023
                    }
                    $arr_grades = Set::extract('/Grades/.', $this->Grades->find("all", array("fields" => array("grade_code"), "Conditions" => array("status" => 1))));
                    $grades = array();
                    foreach ($arr_grades as $vals) {
                        $grades[] = trim($vals['grade_code']); //Edited by Akshay on 30-11-2023
                    }
                    $company_code = $this->Session->read('company_code');
                    App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
                    $empcsvdata = new EmployeeCSVData();
                    //edited by athira on 18-07-2025
                    $arr_empdetails_fields = $empcsvdata->getFieldNames('NewEmployeeDetails');
                    $arr_empdetails_labels = $arr_empdetails_fields;

                    if (in_array($company_code, ['DEMO', 'GLET'])) {
                        if (isset($arr_empdetails_labels['id_card'])) {
                            $arr_empdetails_labels['id_card'] = 'Aadhaar No';
                        }
                        if (isset($arr_empdetails_labels['company_pf'])) {
                            $arr_empdetails_labels['company_pf'] = 'PF No';
                        }
                    }
                    //end


                    $arr_empprof_fields = $empcsvdata->getFieldNames('NewEmployeeProfessionalDetails');
                    $rejectedEmployees = [];

                    foreach ($arrayempdata as $key => $row) {

                        //Edited by athira on 31-12-2024
                        //edited by athira on 20-01-2025

                        // if ($allowedCount > 0) {
                        //     if ($intImportedCount >= $allowedCount) {
                        //         // Collect rejected employees' names
                        //         $rejectedEmployees[] = [
                        //             'name' => $row['First Name'], // Assuming name is a field in the employee data

                        //         ];
                        //         // debug($rejectedEmployees);
                        //         // exit;

                        //         continue; // Skip further processing for this row

                        //     }
                        //     //end
                        // } else {
                        //     echo json_encode(array('success' => 0, 'errors' => $errors, 'msg' => 'Cannot add new employee ! Maximum limit reached'));
                        //     exit;
                        // }

                        $arr_empdetails_data = array();
                        $arr_empdetails_data['emp_pkey'] = 0;
                        $arr_empdetails_data['status'] = 1;
                        $arr_empdetails_data['emp_id'] = '';
                        $str_company_code = isset($authuser['company_code']) ? $authuser['company_code'] : '';
                        $arr_empdetails_data['company_code'] = $str_company_code;
                        $arr_empdetails_data['branch_code'] = $emp_branch;
                        $arr_empdetails_data['nationality_id'] = "75"; //This is to set default nationality as INDIAN. by sinsiya on 07-06-2024
                        //edited by athira on 18-07-2025
                        foreach ($arr_empdetails_labels as $field => $fieldlabel) {

                            $fieldValue = isset($row[$fieldlabel]) ? $row[$fieldlabel] : null;

                            if ($field == 'date_of_birth') {
                                $fieldValue = date("Y-m-d", strtotime($fieldValue));
                            } else if (in_array($field, ['classification', 'maritual_status'])) {
                                $fieldValue = strtolower($fieldValue);
                            }

                            $arr_empdetails_data[$field] = $fieldValue;
                        }
                        //end

                        //Check for, if employee exists or not
                        if (isset($arr_empdetails_data['first_name']) && isset($arr_empdetails_data['last_name']) && isset($arr_empdetails_data['date_of_birth'])) {
                            $arrDuplicateList = $this->EmployeeDetails->find(
                                'all',
                                array(
                                    'fields' => 'EmployeeDetails.emp_pkey',
                                    'conditions' => array(
                                        'first_name' => $arr_empdetails_data['first_name'],
                                        'last_name' => $arr_empdetails_data['last_name'],
                                        'date_of_birth' => $arr_empdetails_data['date_of_birth'],
                                        'status' => 1
                                    )
                                )
                            );
                            if (count($arrDuplicateList) > 0) {
                                $arrDuplicateEmpList[] = $arrDuplicateList[0]['EmployeeDetails']['emp_pkey'];
                                continue; //Take next employee record, since this employee already exixts
                            }
                        }
                        $arr_empprof_data = array();
                        $arr_empprof_data['emp_proff_pkey'] = 0;

                        //$arr_empprof_data['emp_id'] = ''; //$str_company_code.$user_id; 
                        $arr_empprof_data['emp_branch'] = $emp_branch;

                        foreach ($arr_empprof_fields as $field => $fieldlabel) {
                            if ($field == 'joining_date') {
                                $fieldValue = $row[$fieldlabel];
                                //$fieldValue = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($fieldValue));
                                $fieldValue = date("Y-m-d", strtotime($fieldValue));
                                //$fieldValue = substr($fieldValue, 4, 4) . '-' . substr($fieldValue, 2, 2) . '-' . substr($fieldValue, 0, 2);
                                //$fieldValue = substr($fieldValue, 6, 4) . '-' . substr($fieldValue, 3, 2) . '-' . substr($fieldValue, 0, 2);
                            } elseif ($field == 'emp_grade') {  //Edited by Akshay on 8-2-2024
                                $cur_grade = $row[$fieldlabel];
                                // debug($cur_grade);
                                $arr_grades =  $this->EmployeeDetails->query("SELECT grade_pkey FROM grade WHERE grade_code = '$cur_grade' AND status = 1");
                                //edited by sinsiya 13-03-2024
                                if (empty($arr_grades)) {
                                    // Grade not found in table, add error message and continue to next row
                                    $errors[] = "";
                                    continue;
                                } else {
                                    $fieldValue = isset($arr_grades[0]['grade']['grade_pkey']) ? $arr_grades[0]['grade']['grade_pkey'] : 0;
                                    $cur_grade = $fieldValue;
                                }
                            } else {
                                $fieldValue = $row[$fieldlabel];
                            }
                            $arr_empprof_data[$field] = $fieldValue;
                        }
                        //Edited by Akshay on 29-11-2023
                        $id_card = isset($arr_empdetails_data['id_card']) ? $arr_empdetails_data['id_card'] : '';
                        $arrDuplicateIdCardList = $this->EmployeeDetails->find(
                            'all',
                            array(
                                'fields' => 'EmployeeDetails.emp_pkey',
                                'conditions' => array(
                                    'id_card' => $id_card,
                                    'status' => 1
                                )
                            )
                        );

                        if (!empty($arrDuplicateIdCardList)) {


                            $duplicate_id[] = $arr_empdetails_data['first_name'];

                            continue;
                        } /*Ended*/

                        if (in_array($arr_empprof_data['designation'], $desigantions)) {
                        } else {
                            $errors[] = $arr_empdetails_data['first_name'];

                            continue;
                        }
                        if (in_array($arr_empprof_data['emp_dept'], $departments)) {
                        } else {
                            $errors[] = $arr_empdetails_data['first_name'];

                            continue;
                        }
                        //                        if ($arr_empprof_data['emp_grade']) {
                        //                            if (in_array($arr_empprof_data['emp_grade'], $grades)) {
                        //                            } else {
                        //                                $errors[] = $arr_empdetails_data['first_name'];
                        //                                continue;
                        //                            }
                        //                        }


                        //  try {

                        $result1 = $this->EmployeeDetails->save($arr_empdetails_data);

                        /*  } catch (Exception $ex) {
                            $message = "Employee Details Saving Failed " . $ex->getMessage();
                            $errors[] = $arr_empdetails_data['first_name'];
                           continue;
                        } catch (mysqli_sql_exception $ex) {
                           $message = "Employee Credentials Error " . $ex->getMessage();
                            $errors[] = $arr_empdetails_data['first_name'];
                            continue;
                        } */


                        $intImportedCount++;

                        if (!empty($result1)) {
                            $pkey = $this->EmployeeDetails->getLastInsertID();

                            $arr_user_cred = array();
                            $arr_user_cred['user_pkey'] = 0;
                            if ($pkey > 0) {

                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                                $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = isset($arr_empdetails_data['first_name']) ? $arr_empdetails_data['first_name'] : '';
                                $arr_user_cred['last_name'] = isset($arr_empdetails_data['last_name']) ? $arr_empdetails_data['last_name'] : '';
                                $arr_user_cred['middle_name'] = isset($arr_empdetails_data['middile_name']) ? $arr_empdetails_data['middile_name'] : '';
                                $arr_user_cred['email'] = isset($arr_empdetails_data['email']) ? $arr_empdetails_data['email'] : '';
                                $arr_user_cred['phone'] = isset($arr_empdetails_data['mobile_no']) ? $arr_empdetails_data['mobile_no'] : '';

                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                //  try {
                                $result = $this->UserCredentials->save($arr_user_cred);
                                /*   } catch (Exception $ex) {
                                    $message = "Employee Credentials Saving Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } catch (mysqli_sql_exception $ex) {
                                    $message = "Employee Credentials Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } */


                                $arr_empprof_data['emp_fkey'] = $pkey;
                                $arr_empprof_data['notice_days'] = '30';
                                // try {
                                $result2 = $this->EmployeeProfessionalDetails->save($arr_empprof_data);
                                //Edited by Akshay on 8-2-2024
                                if (isset($cur_grade)) {
                                    try {
                                        $user_id = $this->Session->read('login_user_id');
                                        $result3 =  $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('GRADE','$pkey','$cur_grade','$user_id') ");
                                    } catch (Exception $e) {
                                        debug($e);
                                    }
                                }
                                /*  } catch (Exception $ex) {
                                    $message = "Employee Proffessioanls Saving Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } catch (mysqli_sql_exception $ex) {
                                    $message = "Employee Proffessioanls Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } */
                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $outputParameter = array();
                                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                                $outputParameter[] = "'" . $emp_branch . "'";
                                $outputParameter[] = "''";
                                try {
                                    $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                                    $company = strtoupper($this->Session->read('company_code'));
                                    if ($company == 'VSFS') {
                                        $empdeviceid = $this->UserCredentials->query("select SUBSTRING(max(trim(emp_company_id)),3, 10) as deviceid from emp_proff 
                                            where emp_company_id like '%VS%' AND emp_company_id NOT REGEXP 'VSFS' and emp_fkey!=$pkey");
                                        $empdeviceid1 = $empdeviceid['0']['0']['deviceid'];
                                        $empdeviceid1++;
                                        $this->EmployeeProfessionalDetails->query("update emp_proff set emp_company_id = concat('VS',$empdeviceid1) where emp_fkey=$pkey");
                                    }
                                } catch (Exception $ex) {
                                    $message = "Employee Saving Error " . $ex->getMessage();
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    $this->restsave($pkey);
                                    continue;
                                } catch (mysqli_sql_exception $ex) {
                                    $message = "Employee Saving Error " . $ex->getMessage();
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    $this->restsave($pkey);
                                    continue;
                                }
                            }
                        }
                    }
                }
                //edited by athira on 03-01-2025
                $this->Session->write('rejectedEmployees', $rejectedEmployees);

                //end

                unlink($targetpath);
                echo json_encode(array('success' => 1, 'errors' => $errors, 'msg' => $message, 'imported_count' => $intImportedCount, 'duplicate_emp_list' => $arrDuplicateEmpList, 'duplicate_id' => $duplicate_id));
                exit;
            } else {
                unlink($targetpath);
                echo json_encode(array('success' => 0, 'errors' => $errors, 'msg' => 'Sorry, employee data import failed, no data found!', 'imported_count' => $intImportedCount, 'duplicate_emp_list' => 0));
                exit;
            }
        } else {
            echo json_encode(array('success' => 0, 'errors' => $errors, 'msg' => 'Sorry, employee data import failed!', 'imported_count' => $intImportedCount, 'duplicate_emp_list' => 0));
            exit;
        }
    }


    public function getcurrentemployeekey()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $sessionObj = $this->Session->read("Auth.User");
        if (isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2) {
            return json_encode(array('success' => true, 'empPkey' => $sessionObj['emp_fkey']));
        }
        return json_encode(array('success' => false));
    }

    public function showsalaryupload() {}

    /*
     * Employee CTC Upload form
     * By santhosh on 24 Oct 2015
     */

    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $arrear = '', $payout = '', $startdate = '', $employee = '', $approved_by = 0) //Edited by Akshay on 15-10-2024
    {
        $this->autoRender = FALSE;
        //Edited by Akshay on 19-10-2024
        $ctcuploadtype = ($ctcuploadtype === 'undefined') ? 0 : $ctcuploadtype;
        $branch = ($branch === 'undefined') ? '' : $branch;
        $arrear = ($arrear === 'undefined') ? '' : $arrear;
        $payout = ($payout === 'undefined') ? '' : $payout;
        $startdate = ($startdate === 'undefined') ? '' : $startdate;
        $employee = ($employee === 'undefined') ? '' : $employee;
        $approved_by = ($approved_by === 'undefined') ? 0 : $approved_by;

        // debug($ctcuploadtype);
        // debug($branch);
        // debug($arrear);
        // debug($payout);
        // debug($startdate);
        // debug($employee);
        // debug($approved_by);exit;
        //End

        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_gross.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        if ($ctcuploadtype == 1) {
            $worksheet = $objPHPExcel->getActiveSheet();
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Company ID");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(4, 1, "Start Date Effective(yyyy-mm)");
            $worksheet->setCellValueByColumnAndRow(5, 1, "Next Increment Date(yyyy-mm-dd)"); //edited by anukrishnan_03-02-2025
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(27);
        } else {
            $worksheet = $objPHPExcel->getActiveSheet();
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Company ID");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(4, 1, "New Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(5, 1, "Start Date Effective(yyyy-mm)");
            $worksheet->setCellValueByColumnAndRow(6, 1, "Arrear Need");
            $worksheet->setCellValueByColumnAndRow(7, 1, "Payout Month");
            $worksheet->setCellValueByColumnAndRow(8, 1, "Next Increment Date(yyyy-mm-dd)"); //edited by anukrishnan_03-02-2025
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(27); //edited by anukrishnan_03-02-2025
        }
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);

        $cond = '';
        //Edited by Akshay on 15-10-2024
        if ($approved_by != 0) {
            $cond .= " AND au.approved_by = '$approved_by'";
        }
        //End
        //        if (isset($employee) && !empty($employee) && $employee != 'null') {
        //            $cond.= " AND  EmployeeDetails.emp_pkey= if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        //        }
        //edited by megha on 24_07_2019 branch wise excel download
        if ($employee == '0') {
            $cond .= " AND  EmployeeDetails.emp_pkey = EmployeeDetails.emp_pkey ";
        } else if (isset($employee)) {
            $cond .= " AND  EmployeeDetails.emp_pkey = if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }
        //edited by megha on 24_07_2019 branch wise excel download
        if ($branch == '0') {
            $cond .= " AND  EmployeeDetails.branch_code= EmployeeDetails.branch_code ";
        } else if (isset($branch)) {
            $cond .= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $cond .= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }
        if ($ctcuploadtype == 2) {
            $arr_empdetails = $this->EmployeeDetails->query(
                " SELECT UserCredentials.user_id, EmployeeInfo.EmpName,EmployeeInfo.employee_id,ect.emp_anual_ctc,au.start_date_effective "
                    . "FROM emp_details AS EmployeeDetails "
                    . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                    . " LEFT JOIN emp_ctc_transaction AS ect ON (EmployeeDetails.emp_pkey = ect.emp_fkey) "
                    . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                    . "INNER join emp_ctc_upload au on (EmployeeDetails.emp_pkey = au.emp_fkey) "
                    . " WHERE EmployeeDetails.status = ' 1 ' "
                    . " and au.emp_ctc_upload_pkey in (select ctc_upload_fkey 
               from emp_ctc_transaction
                where end_date_effective is null or end_date_effective >= current_date) "
                    . " AND ect.end_date_effective is null  $cond  group by UserCredentials.user_id "
            );
        } else {
            $arr_empdetails = $this->EmployeeDetails->query(
                " SELECT UserCredentials.user_id,EmployeeInfo.employee_id, EmployeeInfo.EmpName "
                    . "FROM emp_details AS EmployeeDetails "
                    . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                    . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                    . " WHERE EmployeeDetails.status = ' 1 '  $cond  group by UserCredentials.user_id "
            );
        }
        $rowindex = 2;
        $columnindex = 0;
        if ($ctcuploadtype == 1) {
            foreach ($arr_empdetails as $value) {
                // debug($value);
                $userid = $value['UserCredentials']['user_id'];
                $empname = $value['EmployeeInfo']['EmpName'];
                $employee_company_id = $value['EmployeeInfo']['employee_id'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $empname);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $employee_company_id);
                $rowindex++;
            }
        } else {
            foreach ($arr_empdetails as $value) {
                // debug($value);
                $userid = $value['UserCredentials']['user_id'];
                $empname = $value['EmployeeInfo']['EmpName'];
                $ctc = $value['ect']['emp_anual_ctc'];
                $employee_company_id = $value['EmployeeInfo']['employee_id'];
                // $sdate = $value['au']['start_date_effective'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $empname);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $employee_company_id);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowindex, $ctc);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowindex, $ctc);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowindex, $startdate);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowindex, $arrear);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowindex, $payout);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowindex, $incrementdate);
                $rowindex++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('Annual Salary Upload'); //Edited by Akshay on 10-10-2024
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    // public function uploadandsaveempctc($ctcuploadtype = 0, $approved_by = 0)
    // {

    //     $this->autoRender = FALSE;
    //     // debug($ctcuploadtype);
    //     //debug($ctcuploadtype);
    //     if ($ctcuploadtype != 0) {
    //         //  echo "hi" ;
    //         $authuser['company_code'] = $this->Session->read('company_code');
    //         $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
    //         $targetpath = getcwd() . "/files/" . $filename;
    //         if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

    //             App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

    //             $objReader = new PHPExcel_Reader_Excel2007();
    //             $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

    //             $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
    //             $lastColumn++;
    //             $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    //             $arrayempdata = array();
    //             $mandatory_fields_warning = FALSE;
    //             if ($highestRowIndex > 1) {
    //                 //edited by megha on 06_08_2019 integration- revision upload condition start
    //                 if ($lastColumn == 'F') {
    //                     $ctcuploadtype = 1;
    //                 } else {
    //                     $ctcuploadtype = 2;
    //                 }
    //                 // end integration- revision upload condition
    //                 //atleast one employee records found
    //                 $index = 0;
    //                 for ($row = 1; $row <= $highestRowIndex; $row++) {
    //                     if ($row == 1) {
    //                         //Get mandatory headings array here
    //                         $array_mandatory_columns = array();
    //                         $array_mandatory_column_names = array('Employee ID', 'Employee Name', 'Start Date Effective(yyyy-mm)'); //Edited by Akshay on 21-10-2024
    //                         for ($col = 'A'; $col != $lastColumn; $col++) {
    //                             $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
    //                             if (in_array($value, $array_mandatory_column_names)) {
    //                                 array_push($array_mandatory_columns, $col);
    //                             }
    //                         }
    //                     } else {
    //                         for ($col = 'A'; $col != $lastColumn; $col++) {
    //                             if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
    //                                 $mandatory_fields_warning = true;
    //                                 break 2;
    //                             }
    //                             $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
    //                             $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
    //                         }
    //                         $index++;
    //                     }
    //                 }
    //                 if ($mandatory_fields_warning) {
    //                     //Exit if mandatory fields not entered
    //                     unlink($targetpath);
    //                     echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
    //                     exit;
    //                 } else {
    //                     //Iam here now
    //                     //debug($arrayempdata);
    //                     //Continue with save if mandatory field warning is not there
    //                     //Save employee ctc and return success
    //                     $this->UserCredentials->useDbConfig = $this->Session->read('ds');
    //                     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //                     $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
    //                     $this->Promotion->useDbConfig = $this->Session->read('ds'); //Edited by Akshay on 21-10-2024

    //                     App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
    //                     //                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
    //                     //                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
    //                     //                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
    //                     //                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');
    //                     //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 1
    //                     $count = 0;

    //                     foreach ($arrayempdata as $key => $row) {
    //                         // debug($arrayempdata);
    //                         // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
    //                         if ($ctcuploadtype == 1) {
    //                             if ($row['Annual Gross Salary'] == '0') {
    //                                 continue;
    //                             }
    //                             $emp_anual_ctc = isset($row['Annual Gross Salary']) ? $row['Annual Gross Salary'] : '';
    //                             $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';
    //                         } else {
    //                             if ($row['New Annual Gross Salary'] == '0') {
    //                                 continue;
    //                             }
    //                             $emp_anual_ctc = isset($row['New Annual Gross Salary']) ? $row['New Annual Gross Salary'] : '';
    //                             $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';
    //                             //$arrear = isset($row['Arrear Need']) ? $row['Arrear Need'] : '';
    //                             //$payout = isset($row['Payout Month']) ? $row['Payout Month'] : '';
    //                         }

    //                         $date = '';
    //                         $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
    //                         if ($user_id == '') {
    //                             continue;
    //                         }
    //                         //debug($emp_anual_ctc);
    //                         //fetch emp_fkey using user_id
    //                         $arr_usercredentials = $this->UserCredentials->find('first', array(
    //                             'fields' => 'emp_fkey',
    //                             'conditions' => array(
    //                                 'user_id' => $user_id
    //                             )
    //                         ));
    //                         $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';
    //                         // debug($arr_empctc_data);
    //                         $arr_empctc_data = array();

    //                         //Edited by Akshay on 21-10-2024
    //                         $arr_empctc_data['promotion_pkey'] = '0';
    //                         $arr_empctc_data['emp_fkey'] = $emp_fkey;
    //                         $arr_empctc_data['annual_gross'] = $emp_anual_ctc;
    //                         $arr_empctc_data['arrear_salary'] = $row['New Annual Gross Salary'];
    //                         $arr_empctc_data['pay_out_month'] = $row['Payout Month'];
    //                         $arr_empctc_data['approved_by'] = $approved_by;
    //                         $arr_empctc_data['remarks'] = 'Salary revision';
    //                         $arr_empctc_data['created_date'] = date('Y-m-d');
    //                         $arr_empctc_data['promotion_status'] = 'APPLIED';
    //                         $arr_empctc_data['created_by'] =  $this->Session->read('login_user_id');

    //                         //End

    //                         //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 2
    //                         if ($emp_anual_ctc != '') {
    //                             $count++;
    //                         }
    //                         $arr_empctc_data['start_date_effective'] = ($start != '') ? date("Y-m-1", strtotime($start)) : null; //Edited by Akshay on 21-10-2024
    //                         // var_dump()
    //                         //debug($arr_empctc_data);
    //                         //                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
    //                         //                                $fieldValue = $row[$fieldlabel];
    //                         //                                $arr_empctc_data[$field] = $fieldValue;
    //                         //                               // debug($arr_empctc_data);
    //                         //                            }


    //                         try {
    //                             // debug($arr_empctc_data);
    //                             // die();
    //                             $result1 = $this->EmployeeCTC->save($arr_empctc_data);

    //                             //Edited by Akshay on 21-10-2024
    //                             // $result1 = $this->Promotion->save($arr_empctc_data);
    //                             // var_dump($result1); exit;
    //                             //End

    //                             //Update salary structure for employee by uploaded CTC
    //                             //On 20 Sep 2016
    //                             //arun 15-10-2016 based on ashoakn
    //                             //                                $this->updateSalStructureDistributionFn($emp_fkey);
    //                         } catch (Exception $e) {
    //                             //debug($e);
    //                         }
    //                     }
    //                 }
    //                 unlink($targetpath);
    //                 //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 3
    //                 if ($count > 0) {
    //                     if ($ctcuploadtype == 1) {
    //                         echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary imported successfully'));
    //                         exit;
    //                     } else {
    //                         echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary reviced successfully'));
    //                         exit;
    //                     }
    //                     //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 4
    //                 } else {
    //                     echo json_encode(array('success' => 0, 'msg' => 'Please Enter a value.'));
    //                     exit;
    //                 }
    //                 //end annual gross salary amount empty excel msg
    //             } else {
    //                 unlink($targetpath);
    //                 if ($ctcuploadtype == 1) {
    //                     echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed, no data found!'));
    //                     exit;
    //                 } else {
    //                     echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed, no data found!'));
    //                     exit;
    //                 }
    //             }
    //         } else {
    //             if ($ctcuploadtype == 1) {
    //                 echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
    //                 exit;
    //             } else {
    //                 echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
    //                 exit;
    //             }
    //         }
    //     } else {
    //         if ($ctcuploadtype == 1) {
    //             echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
    //         } else {
    //             echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
    //             exit;
    //         }
    //         exit;
    //     }
    // }

    public function uploadandsaveempctc($ctcuploadtype = 0)
    {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);
        //debug($ctcuploadtype);
        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //edited by megha on 06_08_2019 integration- revision upload condition start
                    if ($lastColumn == 'F') {
                        $ctcuploadtype = 1;
                    } else {
                        $ctcuploadtype = 2;
                    }
                    // end integration- revision upload condition
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID', 'Employee Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        //                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
                        //                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
                        //                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
                        //                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');
                        //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 1
                        $count = 0;

                        foreach ($arrayempdata as $key => $row) {
                            // debug($arrayempdata);
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($ctcuploadtype == 1) {
                                if ($row['Annual Gross Salary'] == '0') {
                                    continue;
                                }
                                $emp_anual_ctc = isset($row['Annual Gross Salary']) ? $row['Annual Gross Salary'] : '';
                                // $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';
                            } else {
                                if ($row['New Annual Gross Salary'] == '0') {
                                    continue;
                                }
                                $emp_anual_ctc = isset($row['New Annual Gross Salary']) ? $row['New Annual Gross Salary'] : '';
                                // $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';

                                //$arrear = isset($row['Arrear Need']) ? $row['Arrear Need'] : '';
                                //$payout = isset($row['Payout Month']) ? $row['Payout Month'] : '';
                            }

                            $date = '';
                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($user_id == '') {
                                continue;
                            }
                            //debug($emp_anual_ctc);
                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            ));
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';
                            // debug($arr_empctc_data);
                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_ctc_upload_pkey'] = 0;
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['created_date'] = date('Y-m-d');
                            $arr_empctc_data['emp_anual_ctc'] = $emp_anual_ctc;
                            // $arr_empctc_data['arrear_salary'] = $arrear;
                            //$arr_empctc_data['pay_out_month'] = $payout;

                            //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 2
                            if ($emp_anual_ctc != '') {
                                $count++;
                            }
                            // var_dump($start); 
                            $arr_empctc_data['start_date_effective'] = date('Y-m-d H:i:s'); //edited by anukrishnan 15-02-2025
                            // var_dump($arr_empctc_data['start_date_effective']); exit;

                            //edited by anukrishnan_03-02-2025 open
                            if (isset($row['Next Increment Date(yyyy-mm-dd)']) && is_numeric($row['Next Increment Date(yyyy-mm-dd)'])) {
                                $next_increment_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row['Next Increment Date(yyyy-mm-dd)']));
                            } else {
                                $next_increment_date = ''; // Handle invalid or empty values
                            }
                            $arr_empctc_data['next_increment_date'] = $next_increment_date;
                            $query = $this->EmployeeCTC->query("
                                SELECT ei.branch, ei.designation, ei.department
                                FROM employee_info ei
                                JOIN emp_ctc_upload ectc ON ei.emp_pkey = ectc.emp_fkey
                                WHERE ectc.emp_fkey = $emp_fkey
                            ");
                            $branch = $query[0]['ei']['branch'];
                            $designation = $query[0]['ei']['designation'];
                            $department = $query[0]['ei']['department'];

                            $arr_empctc_data['branch'] = $branch;
                            $arr_empctc_data['designation'] = $designation;
                            $arr_empctc_data['department'] = $department;
                            //edited by anukrishnan_03-02-2025 close

                            // var_dump($next_increment_date); exit;

                            //debug($arr_empctc_data);
                            //                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
                            //                                $fieldValue = $row[$fieldlabel];
                            //                                $arr_empctc_data[$field] = $fieldValue;
                            //                               // debug($arr_empctc_data);
                            //                            }


                            try {
                                // debug($arr_empctc_data);
                                // die();
                                // var_dump("huuu");
                                $result1 = $this->EmployeeCTC->save($arr_empctc_data);
                                // var_dump($result1); exit;
                                //Update salary structure for employee by uploaded CTC
                                //On 20 Sep 2016
                                //arun 15-10-2016 based on ashoakn
                                //                                $this->updateSalStructureDistributionFn($emp_fkey);
                            } catch (Exception $e) {
                                //debug($e);
                            }
                        }
                    }
                    unlink($targetpath);
                    //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 3
                    if ($count > 0) {
                        if ($ctcuploadtype == 1) {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary imported successfully'));
                            exit;
                        } else {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary reviced successfully'));
                            exit;
                        }
                        //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 4
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Please Enter a value.'));
                        exit;
                    }
                    //end annual gross salary amount empty excel msg
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
                exit;
            }
            exit;
        }
    }

    //popup for save and update
    public function form()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        //        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));     
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='$cur_emp_branch' ";
        } else {
            $branch_condition = "";
        }
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
            //edited by athira on 02-02-2025
            $emp_pkey = $this->Session->read('emp_fkey');
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " and emp_details.branch_code='$is_ho' ";
            }
            //end
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $branch_condition = " and emp_details.branch_code!='$branch' ";
        }
        //employee branch wise sorting ends here
        //Employee Company ID added by ***ARUL P DAS on 12/12/2019

        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1' . $branch_condition . ' order by first_name ASC');
        $this->set("arr_employees", $arr_employees);
        $data['emp_ctc_upload_pkey'] = 0;
        $data['emp_fkey'] = '';
        $data['emp_anual_ctc'] = '';
        $data['emp_monthly_ctc'] = '';
        $data['emp_loan_balance'] = '';
        $data['emp_advance'] = '';
        $data['emp_tds_deducted'] = '';
        if (isset($_REQUEST['emp_ctc_upload_pkey']) && $_REQUEST['emp_ctc_upload_pkey'] != 0) {
            $data_db = $this->EmployeeCTC->find("first", array("conditions" => array("emp_ctc_upload_pkey" => $_REQUEST['emp_ctc_upload_pkey'])));
            $data = $data_db['EmployeeCTC'];
        }
        $this->set("data", $data);
        //$this->layout = null;
    }

    //save 
    public function employeesave()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // edited by anukrishnan_04-02-2025 open
        $emp_key = $arr_form_data['emp_fkey'];
        $query = $this->EmployeeCTC->query("
            SELECT ei.branch, ei.designation, ei.department
            FROM employee_info ei
            JOIN emp_ctc_upload ectc ON ei.emp_pkey = ectc.emp_fkey
            WHERE ectc.emp_fkey = $emp_key
        ");

        $branch = $query[0]['ei']['branch'];
        $designation = $query[0]['ei']['designation'];
        $department = $query[0]['ei']['department'];
        // edited by anukrishnan_04-02-2025 close


        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        //arun 15-10-2016
        // edited by anukrishnan_15-02-2025 open
        // $date = $arr_form_data['start_date_effective'];

        // $month = explode("-", $date);
        // $year = $month[0];
        // $mon = $month[1];
        // $dat = 1;
        // $set_month = $year . '-' . $mon . '-' . $dat;
        $arr_form_data['start_date_effective'] = date('Y-m-d H:i:s');
        // edited by anukrishnan_15-02-2025 close
        $arr_form_data['next_increment_date'] = date('Y-m-d', strtotime($arr_form_data['next_increment_date'])); // edited by anukrishnan_01-02-2025
        $arr_form_data['branch'] = $branch;   // edited by anukrishnan_04-02-2025
        $arr_form_data['designation'] = $designation;    // edited by anukrishnan_04-02-2025
        $arr_form_data['department'] = $department;     // edited by anukrishnan_04-02-2025

        try {
            $result = $this->EmployeeCTC->save($arr_form_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Employee Gross Salary Added successfully";
            echo json_encode($resp);
        } catch (Exception $ex) {
            var_dump("hii");
            exit;
            $resp = array();
            $resp["success"] = false;
            $resp["msg"] = "Employee Gross Salary Added Failed";
            echo json_encode($resp);
        }

        //Update salary structure for employee by newly added CTC
        //On 20 Sep 2016
        //$emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : '';
        //arun 15-10-2016
        //$this->updateSalStructureDistributionFn($emp_fkey);
    }

    //datagridelist     
    //edited by athira on 30-01-2025       
    public function employeelist()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        // debug($arr_request_data);
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $arrear = isset($arr_request_data['arrear']) ? $arr_request_data['arrear'] : '';
        // debug($branch_code);
        $no_arrear = isset($arr_request_data['no_arrear']) ? $arr_request_data['no_arrear'] : '';
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_pkey = $this->Session->read('emp_fkey');
        $limit = $_REQUEST['rows'];
        // $limit = round($limit / 2); //Edited by Akshay on 24-10-2024
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $branch_condition = '';
        $emp_condition = '';
        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        //debug($arr_request_data);
        //        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
        //            $emp = $arr_request_data['employee'];
        //            $emp_condition = "and au.emp_fkey=$emp";
        //        } else {
        //            $emp_condition = ' ';
        //        }
        //        if ($branch_code != '') {
        //            $branch_condition = "and ed.branch_code='$branch_code'";
        //        }
        //debug($emp_fkey);
        // debug($branch_code);
        //edited by megha on 29_07_2019 branch wise pagination  
        if ($emp_fkey == '' || $emp_fkey == '0') {
            $emp_condition = "";
        } else if (isset($emp_fkey)) {
            $emp = $emp_fkey;
            $emp_condition = "and au.emp_fkey=$emp";
        }


        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and ed.branch_code='" . $cur_emp_branch . "'";
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $branch_condition = "and ed.branch_code!='$branch' and ed.branch_code='$branch_code'";
        } else {
            if ($branch_code == '0' || $branch_code == '') {
                $branch_condition = "";
            } else if (isset($branch_code)) {
                $branch_condition = "and ed.branch_code='$branch_code'";
            }
        }
        if ($user_group == 2) {
            $payroUser = $this->EmployeeCTC->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " and ed.branch_code='$is_ho' ";
            }
        }
        $param = '';
        if (isset($arr_request_data['emp'])) {
            // $param = "and ed.first_name like '%" . $arr_request_data['emp'] . "%'";
            // // debug($param);
            $param = "and (ed.first_name like '%" . $arr_request_data['emp'] . "%' OR ei.employee_id like '%" . $arr_request_data['emp'] . "%')";
            // debug($param);
        }
        //        if ($emp_fkey != '') {
        //            $emp_condition = "and au.emp_fkey=$emp_fkey";
        //        }
        //debug($emp_condition);



        $counts = $this->EmployeeCTC->query(
            "select
            COUNT(*)
             from emp_ctc_upload au
                            INNER join emp_details ed on (ed.emp_pkey = au.emp_fkey)
                            INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
                            where au.status=1 
                  $emp_condition $param "
                . " $branch_condition"
                . " and au.emp_ctc_upload_pkey in (select ctc_upload_fkey "
                . " from emp_ctc_transaction"
                . " where end_date_effective is null or end_date_effective >= current_date)"
        );

        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeCTC->query(
            "select
            ed.emp_pkey,au.*,ei.employee_id,ei.EmpName
             from emp_details ed
                            INNER join emp_ctc_upload au on (ed.emp_pkey = au.emp_fkey)
                            INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
                            where au.status=1 
                $emp_condition $param"
                . " $branch_condition"
                . " ORDER BY emp_ctc_upload_pkey desc "
                . " limit $limit offset $ofst  "
        );

        // $arr_att = $this->EmployeeCTC->query(
        //     "select
        //     ed.emp_pkey,au.*,ei.employee_id,ei.EmpName
        //      from emp_details ed
        //                     INNER join emp_ctc_upload au on (ed.emp_pkey = au.emp_fkey)
        //                     INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
        //                     where au.status=1 
        //         $emp_condition $param"
        //     . " $branch_condition"
        //     . " and au.emp_ctc_upload_pkey in (select ctc_upload_fkey "
        //     . " from emp_ctc_transaction"
        //     . " where end_date_effective is null or end_date_effective >= current_date) "
        //     . " ORDER BY emp_ctc_upload_pkey desc "
        //     . " limit $limit offset $ofst  ");
        //Edited by Akshay on 24-10-2024
        // edited by anukrishnan_18-02-2025 open
        // $arr_att2 = $this->EmployeeCTC->query("select
        //                         ed.emp_pkey,au.*,ei.employee_id,ei.EmpName
        //                         from emp_details ed
        //                         INNER join promotions au on (ed.emp_pkey = au.emp_fkey)
        //                         INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
        //                         where au.status=1 
        //                         $emp_condition $param 
        //                         $branch_condition
        //                         and au.remarks = 'Salary revision'   
        //                         ORDER BY promotion_pkey desc  limit $limit  offset $ofst ");

        // $arr_att = array_merge(array_values($arr_att), array_values($arr_att2));
        // $count = count($arr_att);
        // edited by anukrishnan_18-02-2025 close
        //End
        $out = array();
        //debug($arr_att);
        // var_dump($arr_att);
        // var_dump($arr_att);
        foreach ($arr_att as $key => $value) {
            // debug($value);
            $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : '';
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
            $out['emp_ctc_upload_pkey'] = isset($value['au']['emp_ctc_upload_pkey']) ? $value['au']['emp_ctc_upload_pkey'] : '';
            $out['emp_fkey'] = $emp_pkey = isset($value['au']['emp_fkey']) ? $value['au']['emp_fkey'] : '';
            $out['emp_loan_balance'] = isset($value['au']['emp_loan_balance']) ? $value['au']['emp_loan_balance'] : '';
            $out['emp_advance'] = isset($value['au']['emp_advance']) ? $value['au']['emp_advance'] : '';
            $out['emp_anual_ctc'] = isset($value['au']['emp_anual_ctc']) ? $value['au']['emp_anual_ctc'] : (isset($value['au']['annual_gross']) ? $value['au']['annual_gross'] : '');
            $out['emp_tds_deducted'] = isset($value['au']['emp_tds_deducted']) ? $value['au']['emp_tds_deducted'] : '';
            $out['start_date_effective'] = isset($value['au']['start_date_effective']) ? date('d-m-Y', strtotime($value['au']['start_date_effective'])) : '';
            // $out['next_increment_date'] = isset($value['au']['next_increment_date']) ? date('d-m-Y', strtotime($value['au']['next_increment_date'])) : '';   //edited by anukrishnan_01-02-2025
            $out['next_increment_date'] = (!empty($value['au']['next_increment_date']) && $value['au']['next_increment_date'] !== '0000-00-00')  //edited by anukrishnan_01-02-2025
                ? date('d-m-Y', strtotime($value['au']['next_increment_date']))
                : '';
            $out['pay_out_month'] = isset($value['au']['pay_out_month']) ? $value['au']['pay_out_month'] : '';
            $out['arrear_salary'] = isset($value['au']['arrear_salary']) ? $value['au']['arrear_salary'] : '';
            $out['status'] = isset($value['au']['status']) ? (($value['au']['status'] == '1') ? 'Approved' : 'Pending') : ''; //Edited by Akshay on 10-10-2024
            //Edited by Akshay on 10-10-2024
            $arr_emp = $this->EmployeeCTC->query("SELECT EmpName FROM employee_info ei WHERE emp_pkey = '$emp_pkey'");
            // var_dump($arr_emp);
            $out['approved_by'] = isset($arr_emp[0]['ei']['EmpName']) ? $arr_emp[0]['ei']['EmpName'] : '';
            // var_dump($out['approved_by']);
            //End
            // var_dump($out);
            $resp_att["rows"][$key] = $out;
            // var_dump($resp_att["rows"][$key]);
        }
        $resp_att["total"] = $count;
        // var_dump($resp_att);
        echo json_encode($resp_att);
    }
    //end
    //main page dropdown 
    //edited by athira on 28-01-2025   
    //edited by athira on 30-01-2025    
    public function ctcupload()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        $emp_pkey = $this->Session->read('emp_fkey');
        //edited by sinsiya
        // $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        // edited by anukrishnan_10-02-2025 open
        $is_ho = 0;
        $this->set('is_ho', $is_ho);
        // edited by anukrishnan_10-02-2025 close
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $this->set('is_ho', $is_ho);
            if ($is_ho != 1) {
                $conditions[] = array("branch_code" => $is_ho, "status" => 1);
            }
        }
        //   debug($conditions);
        //   exit;

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        //$user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions[] = array("branch_code" => $cur_emp_branch, "status" => 1);
        } else {
            $conditions[] = array("status" => 1);
        }
        //edited by sinsiya
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $conditions[] = array("branch_code !=" => $branch);
            // debug($conditions);
        }
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => $conditions)));


        //debug($arr_branches);
        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions)));
        // debug($this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions)));
    }
    //end
    //delete
    public function deleteEmployees()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_ctc_upload_pkey"]) || isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["emp_ctc_upload_pkey"]);
            //debug($ar_ids);
            $this->EmployeeCTC->updateAll(
                array('EmployeeCTC.status' => 0),
                array('EmployeeCTC.emp_ctc_upload_pkey' => $ar_ids)
            );
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }
    public function isLastAddedEmployee()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $result = array('success' => 0);
        $ar_ids = $_REQUEST["emp_pkey"];
        // debug($ar_ids);
        $result = $this->EmployeeDetails->query("SELECT CASE 
    WHEN MAX(emp_pkey) = $ar_ids THEN 'Yes'
    ELSE 'No'
END AS is_last_record FROM emp_details");
        $value = $result[0][0]['is_last_record'];
        $valueofemp = $this->EmployeeDetails->query("Select day_time_seq,LEAVEPOLICY_GROUP_ID,HOLIDAY_GROUP_ID,structure_id from emp_proff where emp_fkey= $ar_ids");
        // debug("Select day_time_seq,LEAVEPOLICY_GROUP_ID,HOLIDAY_GROUP_ID,structure_id from emp_proff where emp_pkey= $ar_ids");
        // debug($value);
        // $response = array('status' => $value);
        $response = array(
            'status' => $value,
            'employee_details' => $valueofemp
        );
        echo json_encode($response);

        //echo json_encode(array('status' => $value));
    }

    public function deleteEmp()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        //$msg = 'Removed by admin on '.date('d-m-Y');
        //edited by athira on 28-02-2025
        $this->EmployeeDetails->updateAll(
             array(
            'EmployeeDetails.status' => 2,
            'EmployeeDetails.attr5' => "'" . date('Y-m-d') . "'",
            'EmployeeDetails.modified_date' => "NOW()"
        ),
            array('EmployeeDetails.emp_pkey' => $ar_ids)
        );
        ///end
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }

    public function  activateEmp()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        $ar_ids = $_REQUEST["ids"];
        $unwantedUsers = $this->EmployeeDetails->find('count', array('conditions' => array('emp_pkey' => $ar_ids, 'status' => 1)));

        $this->EmployeeDetails->updateAll(
             array(
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.modified_date' => "NOW()"
        ),
            array('EmployeeDetails.emp_pkey' => $ar_ids, 'EmployeeDetails.status' => 2)
        );

        if ($unwantedUsers > 0) {
            $result['success'] = 1;
            $result['msg'] = "Selected employee consists of already active records. Employee activated successfully.";
        } else {
            $result['success'] = 1;
            $result['msg'] = "Employee activated successfully.";
        }

        echo json_encode($result);
    }

    public function addqualification($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
    }

    public function addfamily($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
    }

    public function passport($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
    }

    public function savefamily()
    {
        $this->Family->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $this->Family->save($arr_form_data);
        $this->autoRender = FALSE;
    }

    public function savepassport()
    {
        $this->passport->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        debug($arr_form_data);
        $this->passport->save($arr_form_data);
        $this->autoRender = FALSE;
    }

    public function savequalifications()
    {
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        if ($arr_form_data['emp_fkey'] != '') {
            // Edited by Akshay on 29-1-2025
            $user_group = $this->Session->read('user_group');
            $company_code = $this->Session->read('company_code');
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            } else {
                $this->qualifcations->save($arr_form_data);
            }
            // End
        } else {
        }
        $this->autoRender = FALSE;
    }

    public function history($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
    }

    public function savehistory()
    {
        $this->history->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        if ($arr_form_data['emp_fkey'] != '') {
            $this->history->save($arr_form_data);
        } else {
        }
        $this->autoRender = FALSE;
    }

    public function lstfamilies($emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->Family->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $resp_emp["data"] = array();
        $this->Family->useDbConfig = $this->Session->read('ds');
        $count = $this->Family->find("count");
        $arr_emp = $this->Family->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $value["Family"]['DOB'] = date("d-m-Y", strtotime($value["Family"]['DOB'])); //Edited by Akshay on 7-3-2024
            $resp_emp["data"][$key] = $value["Family"];
        }

        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);
    }

    public function listhistory($emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');



        $resp_emp = array();
        $resp_emp["data"] = array();
        $this->history->useDbConfig = $this->Session->read('ds');
        $count = $this->history->find("count");
        $arr_emp = $this->history->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $value["history"]["from_date"] = date("d-m-Y", strtotime($value["history"]["from_date"])); //Edited by Akshay on 7-3-2024
            $value["history"]["to_date"] = date("d-m-Y", strtotime($value["history"]["to_date"])); //Edited by Akshay on 7-3-2024
            $resp_emp["data"][$key] = $value["history"];
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    public function getusers($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$emp_fkey = $this->Session->read('emp_fkey');
        $arr_request_data = $this->request->query;
        $searchkey = $arr_request_data['username'];
        $filter_condition = array();
        //        if (isset($arr_request_data['username'])) {
        //           
        //            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" and emp_pkey != ' . $emp_fkey;
        //        } else {
        //            $filter_condition = '';
        //        }
        $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  "
            . "OR EmpName like '%" . $searchkey . "%' OR EmployeeDetails.emp_id like '%" . $searchkey . "%' ";

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'employee_info',
                'alias' => 'EmpInfo',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpInfo.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $arr_users = $this->EmployeeDetails->find(
            'all',
            array(
                'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
                'joins' => $joins,
                'conditions' => array(
                    'status' => 1,
                    $filter_condition
                )
            )
        );

        //  debug($arr_users);
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val[0]) : array();
        }
        //  debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function listqualifications($emp_pkey = '')
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $resp_emp["data"] = array();
        $count = $this->qualifcations->find("count", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $arr_emp = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $resp_emp["data"][$key] = $value["qualifcations"];
        }

        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);
    }

    public function listpassports($emp_pkey = '')
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);
        $this->passport->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $resp_emp["data"] = array();
        $count = $this->passport->find("count", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $arr_emp = $this->passport->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        //        debug($arr_emp);
        foreach ($arr_emp as $key => $value) {
            //edited by megha on 5_6_19 no file display
            if (!empty($value['passport']['files'])) {
                // debug($value['passport']['files']);
                //$value["passport"]["imgs"] = isset($value['passport']['files'])?'<a href="https://login.mypayrollmaster.com/'.$value['passport']['files'].'" download>Download</a>':'No File';
                $value["passport"]["imgs"] = '<a href="https://v1.mypayrollmaster.online/' . $value['passport']['files'] . '" download target="_blank">Download</a>';
            } else {
                $value["passport"]["imgs"] = 'No File';
            }
            //end
            $value["passport"]["valid_from"] = date("d-m-Y", strtotime($value["passport"]["valid_from"])); //Edited by Akshay on 7-3-2024
            $value["passport"]["valid_till"] = date("d-m-Y", strtotime($value["passport"]["valid_till"])); //Edited by Akshay on 7-3-2024
            $resp_emp["data"][$key] = $value["passport"];
        }
        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);
    }

    public function savefile()
    {
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $resp = array();
        $resp['error'] = '';
        $this->autoRender = FALSE;
        try {

            if (
                !isset($_FILES['avatarfile']['error']) ||
                is_array($_FILES['avatarfile']['error'])
            ) {
                $resp['error'] = 'Invalid parameters.';
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['avatarfile']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $resp['error'] = 'No file sent.';
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $resp['error'] = 'Exceeded filesize limit.';
                default:
                    $resp['error'] = 'Unknown errors.';
            }

            // You should also check filesize here. 
            if ($_FILES['avatarfile']['size'] > 1000000) {
                $resp['error'] = 'Exceeded filesize limit.';
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($_FILES['avatarfile']['tmp_name']),
                array(
                    'jpeg' => 'image/jpeg',
                    'jpg' => 'image/jpg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf' => 'application/pdf'
                    //edited by megha on 6/6/19 removed xsl and zip
                    // 'xlxs' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    // 'docx' => 'application/zip',
                    //end
                ),
                true
            )) {
                $resp['error'] = 'Invalid file format.';
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.


            $filename = sprintf(
                'img/avatar/%s.%s',
                sha1_file($_FILES['avatarfile']['tmp_name']),
                $ext
            );
            if (!move_uploaded_file(
                $_FILES['avatarfile']['tmp_name'],
                $filename
            )) {
                $resp['error'] = 'Failed to move uploaded file.';
            }
            //   echo 'File is uploaded successfully.';
            $this->passport->useDbConfig = $this->Session->read('ds');
            /*
              $this->CentralUserCredentials->updateAll(
              array('avatar' => $filename),
              array('user_id' => $this->Session->read('user_pkey'))
              ); */

            $data['user_pkey'] = $this->Session->read('user_pkey');
            $data['avatar'] = $filename;
            $resp['data'] = $data;
            //debug($data);
        } catch (RuntimeException $e) {

            //  echo $e->getMessage();
        }
        echo json_encode($resp);
    }

    public function deletequal($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        if ($this->qualifcations->query("delete from qualifcations where qualification_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function deletepassports($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->passport->useDbConfig = $this->Session->read('ds');
        if ($this->passport->query("delete from emp_passport_visa where emp_passport_visa_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function deletehist($pkey = 0)
    {
        $success = 0;

        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        if ($this->history->query("delete from history where history_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function deletefdetails($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        $details = $this->history->query("SELECT `emp_fkey`, `emergency_contact` FROM `emp_family` WHERE `emp_family_pkey` = '$pkey'");
        $emp_pkey = $details[0]['emp_family']['emp_fkey'];
        $emergency_contact = $details[0]['emp_family']['emergency_contact'];
        if ($emergency_contact == "Y") {
            $this->history->query("UPDATE `settings_runner` SET `exit_status` = 'Next_time' WHERE `emp_fkey` = '$emp_pkey'");
        }
        if ($this->history->query("delete from emp_family where emp_family_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function mark_nominee($pkey = 0, $emp = 0)
    {
        $success = 0;

        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        if ($this->history->query("UPDATE emp_family set is_nominee = 'N' where emp_fkey in('$emp') ")) {
            $success = 1;
        }
        if ($this->history->query("UPDATE emp_family set is_nominee = 'Y' where emp_family_pkey = '$pkey' ")) {
            $success = 1;
        }
    }

    public function mark_emergency($pkey = 0, $emp = 0)
    {
        $resp = array();
        $resp["success"] = false;

        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        $is_contact_exist = $this->history->query("select contact_number,alternate_number from emp_family where emp_family_pkey=$pkey");
        $num1 = $is_contact_exist[0]['emp_family']['contact_number'];
        $num2 = $is_contact_exist[0]['emp_family']['alternate_number'];
        if ($num1 != null || $num2 != null) {
            if ($this->history->query("UPDATE emp_family set emergency_contact = 'N' where emp_fkey in('$emp') ")) {
                $resp["success"] = true;
            }
            if ($this->history->query("UPDATE emp_family set emergency_contact = 'Y' where emp_family_pkey = '$pkey' ")) {
                $resp["success"] = true;
            }

            $setting_key = $this->history->query("select settings_pkey from genaral_setings where description='Emargency Number Collection'");
            $settings_fkey = $setting_key[0]['genaral_setings']['settings_pkey'];
            $exist_check = $this->history->query("select * from settings_runner where emp_fkey=$emp");
            if (isset($exist_check[0]['settings_runner'])) {
                $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
                $this->history->query("update settings_runner set exit_status='Finished',updated_times=$updated,modification_date=now() where emp_fkey=$emp and settings_fkey=$settings_fkey");
                $resp["success"] = true;
            } else {
                $this->history->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp,'Finished',0)");
                $resp["success"] = true;
            }
        } else {
            $resp["msg"] = "Please select any family member with contact number.";
        }
        echo json_encode($resp);
    }

    public function importProfile($id = 0)
    {
        $this->layout = null;
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
    }

    // function sendRequestOTP($empProfileid = '', $email = '')
    // {

    //     $response_histor_add = $this->getDataFromAPI($empProfileid);
    //     // $this->layout = null;

    //     $arr_reponse = "";
    //     $this->set('profileID', $empProfileid);

    //     if (json_decode($response_histor_add)->status != 200) {
    //         $arr_reponse = array();
    //         $this->set('generatedOTP', array("generatedOTP" => 0, "EmpProfileID" => $empProfileid));
    //         $this->render('sendrequestotp');
    //     } else {
    //         $arr_reponse = json_decode($response_histor_add);

    //         App::import('Vendor', 'FirebaseNotification', array('file' => 'FirebaseNotification.php'));

    //         $FCM = new FirebaseNotification();

    //         $userData = $this->getUID($empProfileid, $email);

    //         if (json_decode($userData)->status == 200) {

    //             if (json_decode($userData)->uIPushNotificationKey) {
    //                 $generatedOTP = rand();

    //                 $FCM->heading = "Data Sharing Request";
    //                 $FCM->body = "Hello, Use this OTP to access the permission to share your data. The OTP is " . $generatedOTP;

    //                 $FCM->sendFCM(json_decode($userData)->uIPushNotificationKey);

    //                 $this->set('generatedOTP', array("generatedOTP" => $generatedOTP, "EmpProfileID" => $empProfileid));
    //             } else {
    //                 $this->set('generatedOTP', array("generatedOTP" => 0, "EmpProfileID" => $empProfileid));
    //             }
    //         }



    //         $this->render('sendrequestotp');
    //     }
    // }
    function sendRequestOTP($empProfileid = '', $email = '')
    {
        // Always use POST data (AJAX method)
        if (empty($email)) {
            $email = trim($this->request->data('email'));
        }

        $response_histor_add = $this->getDataFromAPI($empProfileid);
        $this->set('profileID', $empProfileid);

        // API Not OK → stop
        if (json_decode($response_histor_add)->status != 200) {
            $this->set('generatedOTP', ["generatedOTP" => 0, "EmpProfileID" => $empProfileid]);
            return $this->render('sendrequestotp');
        }

        // Load models
        $this->loadModel('EmployeeJoin');
        $this->loadModel('EmployeeDetails');

        // DB config
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeJoin->useDbConfig    = $this->Session->read('ds');

        // Check email in EmployeeDetails
        $existsInDetails = $this->EmployeeDetails->find('count', [
            'conditions' => ['EmployeeDetails.email' => $email]
        ]);

        // Check email in EmployeeJoin
        $existsInJoin = $this->EmployeeJoin->find('count', [
            'conditions' => ['EmployeeJoin.email' => $email]
        ]);

        // ❌ STOP if email already exists
        if ($existsInDetails > 0 || $existsInJoin > 0) {
            $this->set('generatedOTP', [
                "generatedOTP" => 0,
                "EmpProfileID" => $empProfileid,
                "error" => "Email already exists. Please use a different email."
            ]);
            return $this->render('sendrequestotp'); // STOP HERE
        }

        // ---------- Email is unique → Send OTP ---------- //
        App::import('Vendor', 'FirebaseNotification', ['file' => 'FirebaseNotification.php']);
        $FCM = new FirebaseNotification();
        $userData = $this->getUID($empProfileid, $email);

        if (json_decode($userData)->status == 200 && json_decode($userData)->uIPushNotificationKey) {

            $generatedOTP = rand(100000, 999999); // more secure OTP

            $FCM->heading = "Data Sharing Request";
            $FCM->body = "Hello, Use this OTP to access your data. OTP: " . $generatedOTP;

            $FCM->sendFCM(json_decode($userData)->uIPushNotificationKey);

            $this->set('generatedOTP', [
                "generatedOTP" => $generatedOTP,
                "EmpProfileID" => $empProfileid
            ]);
        } else {
            $this->set('generatedOTP', [
                "generatedOTP" => 0,
                "EmpProfileID" => $empProfileid
            ]);
        }

        return $this->render('sendrequestotp');
    }



    function testFCM()
    {

        App::import('Vendor', 'FirebaseNotification', array('file' => 'FirebaseNotification.php'));

        $FCM = new FirebaseNotification();

        $FCM->heading = "Notification Heading";
        $FCM->body = "Hello";

        $FCM->sendFCM('cZkIvOMnR_eGWKMB-rnzFc:APA91bEU8mqfxxo_Hwmreao_AwZUI8SC5aFB42lsWrdrGuIVLG6TlXluPsw0roptJG-L1eyO8i98lEDJP4va7S4ofu9UzmOde8hXbl-BU4tGgFOaUU5k3uhEi7yJi_y3gtg46CbgBcLR');

        $this->autoRender = false;
    }

    function sendFCM($id = '', $body = '', $title = '')
    {

        $this->autoRender = false;

        $registrationIds = array('cZkIvOMnR_eGWKMB-rnzFc:APA91bEU8mqfxxo_Hwmreao_AwZUI8SC5aFB42lsWrdrGuIVLG6TlXluPsw0roptJG-L1eyO8i98lEDJP4va7S4ofu9UzmOde8hXbl-BU4tGgFOaUU5k3uhEi7yJi_y3gtg46CbgBcLR');

        // prep the bundle
        $msg = array(
            'message' => 'This is an test messgae',
            'title'     => 'This is a title. title',
            'subtitle'  => 'This is a subtitle. subtitle',
            'tickerText'    => 'Ticker text here...Ticker text here...Ticker text here',
            'vibrate'   => 1,
            'sound'     => 1,
            'largeIcon' => 'large_icon',
            'smallIcon' => 'small_icon'
        );

        $fields = array(
            // use this to method if want to send to topics
            // 'to' => 'topics/all'
            "notification" => [
                "body" => "Hello",
                "title" => 'Hello test notifications',
                "icon" => "ic_launcher"
            ],
            'registration_ids'  => $registrationIds,
            'data' => $msg
        );

        $headers = array(
            'Authorization: key=AAAA4J_UTUg:APA91bGPDB3OZAPWQOGrn7cUJ7ypsoKwI3tkdTjmIdLpCijnwC3KxNECA1LJ1rghgUdcVqLzcPRiv7vSy1KExN46Ja5aKvI4ueDYj6TLqEtC4d2oGW-0Opv-DdD8ar2uRa9T8vtH1r8V',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send ');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        var_dump($result);
    }

    public function requestPermissionAccess($profileID = '', $branchCode = '')
    {

        $response_histor_add = $this->getDataFromAPI($profileID);
        // $this->layout = null;

        // debug(json_decode($response_histor_add));
        $arr_reponse = "";
        $this->set('profileID', $profileID);

        if (json_decode($response_histor_add)->status != 200) {
            $arr_reponse = array();
        } else {
            $arr_reponse = json_decode($response_histor_add);
        }

        $this->set('arr_reponse', $arr_reponse);
        $this->render('getprofileinfo');
    }

    public function getprofileinfo($profileID = '', $branchCode = '')
    {

        $response_histor_add = $this->getDataFromAPI($profileID);
        // $this->layout = null;

        // debug(json_decode($response_histor_add));
        $arr_reponse = "";
        $this->set('profileID', $profileID);
        $this->set('branchCode', $branchCode);

        if (json_decode($response_histor_add)->status != 200) {
            $arr_reponse = array();
        } else {
            $arr_reponse = json_decode($response_histor_add);
        }

        $this->set('arr_reponse', $arr_reponse);
        $this->render('getprofileinfo');
    }

    function importEmployeetoDatabase()
    {
        $this->autoRender = false;

        $arr_request_data = $this->request->data;
        $response_histor_add = $this->getDataFromAPI($arr_request_data['profileID']);
        // $this->layout = null;

        $company_code = $this->Session->read('company_code');

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $this->passport->useDbConfig = $this->Session->read('ds');
        $this->Family->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        $arr_reponse = "";
        $responseData = json_decode($response_histor_add);

        if ($responseData->status == 200) {
            $arr_employee_data = get_object_vars($responseData->employeeInfo->employeeDetails);
            $qualification = $responseData->employeeInfo->qualification;
            $passportVisa = $responseData->employeeInfo->passportVisa;
            $family = $responseData->employeeInfo->family;

            // var_dump($arr_employee_data);
            $result = $this->EmployeeDetails->save(array(
                "company_code" => $company_code,
                // "branch_code" => $arr_request_data['branchCode'],
                "emp_id" => "",
                "first_name" => isset($arr_employee_data['firstName']) ? $arr_employee_data['firstName'] : '',
                "last_name" => isset($arr_employee_data['lastName']) ? $arr_employee_data['lastName'] : '',
                "middile_name" => isset($arr_employee_data['middleName']) ? $arr_employee_data['middleName'] : '',
                "classification" => $arr_employee_data['gender'] == 'M' ? 'male' : 'female',
                "address" => isset($arr_employee_data['address']) ? $arr_employee_data['address'] : '',
                "city" => isset($arr_employee_data['city']) ? $arr_employee_data['city'] : '',
                "state" => isset($arr_employee_data['state']) ? $arr_employee_data['state'] : '',
                "nationality_id" => isset($arr_employee_data['nationality']) ? $arr_employee_data['nationality'] : '',
                "pincode" => isset($arr_employee_data['pincode']) ? $arr_employee_data['pincode'] : '',
                "mobile_no" => isset($arr_employee_data['mobileNo']) ? $arr_employee_data['mobileNo'] : '',
                "email" => isset($arr_employee_data['email']) ? $arr_employee_data['email'] : '',
                "maritual_status" => isset($arr_employee_data['maritalStatus']) ? $arr_employee_data['maritalStatus'] : '',
                "education" => isset($arr_employee_data['education']) ? $arr_employee_data['education'] : '',
                "date_of_birth" => isset($arr_employee_data['dateOfBirth']) ? date("Y-m-d", strtotime($arr_employee_data['dateOfBirth'])) : '',
                "bank_name" => isset($arr_employee_data['bankName']) ? $arr_employee_data['bankName'] : '',
                "branch_name" => isset($arr_employee_data['bankBranchName']) ? $arr_employee_data['bankBranchName'] : '',
                "branch_address" => isset($arr_employee_data['branchAddress']) ? $arr_employee_data['branchAddress'] : '',
                "name_as_per_bank" => isset($arr_employee_data['nameAsPerBank']) ? $arr_employee_data['nameAsPerBank'] : '',
                "ifsc_code" => isset($arr_employee_data['ifscCode']) ? $arr_employee_data['ifscCode'] : '',
                "account_no" => isset($arr_employee_data['accountNo']) ? $arr_employee_data['accountNo'] : '',
                "pf" => isset($arr_employee_data['pf']) ? $arr_employee_data['pf'] : '',
                "company_pf" => isset($arr_employee_data['companyPf']) ? $arr_employee_data['companyPf'] : '',
                "esi_dispensary" => isset($arr_employee_data['esiDispensary']) ? $arr_employee_data['esiDispensary'] : '',
                "esi" => isset($arr_employee_data['esi']) ? $arr_employee_data['esi'] : '',
                "id_card" => isset($arr_employee_data['idCard']) ? $arr_employee_data['idCard'] : '',
                "guradian" => isset($arr_employee_data['guardian']) ? $arr_employee_data['guardian'] : '',
                "relation_guardian" => isset($arr_employee_data['relationGuardian']) ? $arr_employee_data['relationGuardian'] : '',
                "pan_no" => isset($arr_employee_data['panNo']) ? $arr_employee_data['panNo'] : '',
                "name_as_on_pan" => isset($arr_employee_data['nameAsOnPan']) ? $arr_employee_data['nameAsOnPan'] : '',
                "name_as_on_aadhaar" => isset($arr_employee_data['nameAsOnAadhaar']) ? $arr_employee_data['nameAsOnAadhaar'] : '',
                "parent" => isset($arr_employee_data['parent']) ? $arr_employee_data['parent'] : '',
                "hearing" => isset($arr_employee_data['hearing']) ? $arr_employee_data['hearing'] : '',
                "visual" => isset($arr_employee_data['visual']) ? $arr_employee_data['visual'] : "",
                "physical_handicap" => isset($arr_employee_data['physicalHandicap']) ? $arr_employee_data['physicalHandicap'] : '',
                "previous_member_id" => isset($arr_employee_data['previousMemberId']) ? $arr_employee_data['previousMemberId'] : '',
                "blood" => isset($arr_employee_data['blood']) ? $arr_employee_data['blood'] : "",
                "locomotive" => isset($arr_employee_data['locomotive']) ? $arr_employee_data['locomotive'] : '',
                "status" => 4,
                "international_worker" => isset($arr_employee_data['internationalWorker']) ? $arr_employee_data['internationalWorker'] : ''
            ));


            if (!empty($result)) {
                $message = 'Personal Details Saved Successfully';
                $pkey = $this->EmployeeDetails->getLastInsertID();

                // $this->addToNotice($arr_form_data['notice_days'], $pkey);

                $company_key = $this->Session->read('company_key');
                $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                if ($punch_type && $punch_type == 'device') {
                    //Device available, so generate emp id concatenate with device id and emp id from device
                    $arr_user_cred = array();
                    if ($pkey > 0) {

                        $arr_form_data['emp_fkey'] = $pkey;

                        try {
                            //Save to employee proffessionals table
                            $user_group = $this->Session->read("user_group");
                            $curr_user_id = $this->Session->read('login_user_id');
                            //if (isset($user_group) && $user_group == 2) {
                            $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('GRADE','$pkey','','$curr_user_id') ");
                            //}

                            $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                            //Insert user credentials  
                            //Get company_code 
                            $str_company_code = $this->Session->read('company_code');

                            $emp_username = ''; //No device details here on manually entering emp data 

                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = $emp_username;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_employee_data['firstName'];
                            $arr_user_cred['last_name'] = $arr_employee_data['lastName']; // Removed Field. by Arul P Das on 20-6-21
                            $arr_user_cred['middle_name'] = $arr_employee_data['middleName']; // Removed Field. by Arul P Das on 20-6-21
                            $arr_user_cred['name_as_per_bank'] = $arr_employee_data['nameAsPerBank']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_aadhaar'] = $arr_employee_data['nameAsOnAadhaar']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_pan'] = $arr_employee_data['nameAsOnPan']; // Commonly one name takes for every name fields. updated on 25/06/2021


                            $arr_user_cred['email'] = $arr_employee_data['email'];
                            $arr_user_cred['phone'] = $arr_employee_data['mobileNo'];
                            try {
                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $result = $this->UserCredentials->save($arr_user_cred);
                            } catch (Exception $ex) {
                                $this->restsave($pkey);
                                $message = 'UserCredentials Details Saving Failed';
                                if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                                    $message = 'User Already Exists With the same COMPANYID';
                                return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                            }
                        } catch (Exception $ex) {
                            $this->restsave($pkey);
                            $message = 'Proffessioanl Details Saving Failed';
                            if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                                $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                        }
                    }
                } else {
                    //Manually generate emp id for companies without device 
                    $arr_user_cred = array();
                    $arr_form_data['emp_fkey'] = $pkey;
                    //Save to employee proffessionals table
                    try {
                        $user_group = $this->Session->read("user_group");
                        if (isset($user_group) && $user_group == 2) {
                            $arr_form_data['attr1'] = $cur_emp_key = $this->Session->read("emp_fkey");
                            $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('HIERARCHY','$pkey','$cur_emp_key','$cur_emp_key') ");
                        }

                        $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                        if ($pkey > 0) {
                            //Insert user credentials  

                            $str_company_code = $this->Session->read('company_code');
                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_employee_data['firstName'];
                            $arr_user_cred['last_name'] =  ''; // Removed field. by Arul P Das on 20_6_21
                            $arr_user_cred['middle_name'] =  ''; // Removed field. by Arul P Das on 20_6_21
                            $arr_user_cred['name_as_per_bank'] = $arr_employee_data['nameAsPerBank']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_aadhaar'] = $arr_employee_data['nameAsOnAadhaar']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_pan'] = $arr_employee_data['firstName']; // Commonly one name takes for every name fields. updated on 25/06/2021								
                            $arr_user_cred['email'] = $arr_employee_data['email'];
                            $arr_user_cred['phone'] = $arr_employee_data['mobileNo'];

                            try {
                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $result = $this->UserCredentials->save($arr_user_cred);
                                //Call procedure 'Linkemp_deviceanddatabase'
                                $outputParameter = array();
                                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                                $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'" . $arr_form_data['emp_branch'] . "'" : "''";
                                $outputParameter[] = "''";
                                try {
                                    $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                                    $company = strtoupper($this->Session->read('company_code'));
                                    if ($company == 'VSFS') {
                                        $empdeviceid = $this->UserCredentials->query("select SUBSTRING(max(trim(emp_company_id)),3, 10) as deviceid from emp_proff 
                                            where emp_company_id like '%VS%' AND emp_company_id NOT REGEXP 'VSFS' and emp_fkey!=$pkey");
                                        $empdeviceid1 = $empdeviceid['0']['0']['deviceid'];
                                        $empdeviceid1++;
                                        $this->EmployeeProfessionalDetails->query("update emp_proff set emp_company_id = concat('VS',$empdeviceid1) where emp_fkey=$pkey");
                                    }
                                } catch (Exception $ex) {
                                    $this->restsave($pkey);
                                    $message = 'Link Employee Details Saving Failed';
                                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                }
                            } catch (Exception $ex) {
                                $this->restsave($pkey);
                                $message = 'UserCredentials Details Saving Failed';
                                if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                                    $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                                return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                            }
                        }
                    } catch (Exception $ex) {
                        $this->restsave($pkey);
                        $message = 'Proffessioanl Details Saving Failed';
                        if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                            $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                        return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                    }
                }

                foreach ($qualification as $key => $value) {
                    $result = $this->qualifcations->saveAll(array(
                        "emp_fkey" => $pkey,
                        "course" => $value->course,
                        "university" => $value->university,
                        "duration" => 1,
                        "mark" => $value->mark
                    ));
                    // debug($result);
                }

                foreach ($passportVisa as $key => $value) {
                    $result = $this->passport->saveAll(array(
                        "emp_fkey" => $pkey,
                        "document_type" => $value->documentType,
                        "document_number" => $value->documentNumber,
                        "classification" => $value->classification,
                        "name" => $value->name,
                        "valid_till" => date("Y-m-d", strtotime($value->validTo)),
                        "relation" => $value->relation,
                        "valid_from" => date("Y-m-d", strtotime($value->validFrom)),
                        "nationality" => $value->nationality,
                        "remarks" => $value->remarks,
                        "reccuring" => $value->reccuring,
                    ));
                }

                foreach ($family as $key => $value) {
                    $result = $this->Family->saveAll(array(
                        "emp_fkey" => $pkey,
                        "name" => $value->name,
                        "DOB" => date("Y-m-d", strtotime($value->dateOfBirth)),
                        "gender" => $value->gender,
                        "blood_group" => $value->bloodGroup,
                        "relation" => $value->relation,
                        "nationality" => $value->nationality,
                        "emergency_contact" => $value->emergencyContact,
                        "alternate_number" => $value->alternateNumber,
                        "contact_number" => $value->contactNumber,
                        "is_nominee" => $value->isNominee,
                    ));
                }
            }
        } else {
            $arr_reponse = json_decode($response_histor_add);
        }

        $message = 'UserCredentials Details Saved';
        return json_encode(array('success' => true, 'error' => "", 'pkey' => $pkey, 'message' => $message));
    }


    public function getDataFromAPI($profileID)
    {
        $this->autoRender = false;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online/thirdpartyapi/empDetails/fullInfo?profileId=' . $profileID, //FT114
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function getUID($profileID, $email)
    {
        $this->autoRender = false;
        $curl = curl_init();

        $payloadata = array(
            "profileId" => $profileID,
            "email" => $email //"theinteractivecode@gmail.com"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online//thirdpartyapi/userDetails/pushNotificationKeyVerify', //FT114
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payloadata),
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    //To show emoloyee bulk import response and showing not inserted entries list
    public function showimportresponse($importedCount = '', $strDuplicateEmpKeys = '', $errors = '')
    {
        //debug($errors);
        //edited by athira on 03-02-2025
        $this->set('importedCount', $importedCount);
        $rejectedEmployees = $this->Session->read('rejectedEmployees');
        $this->set('rejectedEmployees', $rejectedEmployees);
        //end


        if ($importedCount > 0) {
            $this->set('title', 'Employees imported successfully');
        } else {
            $this->set('title', 'Sorry, not imported any employees');
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arrDuplicateEmpList = array();
        if ($strDuplicateEmpKeys != '') {
            $arrDuplicateEmpKeys = explode(',', urldecode($strDuplicateEmpKeys));
            $arrDuplicateEmpList = $this->EmployeeDetails->find('all', array('fields' => 'first_name,last_name,date_of_birth', 'conditions' => array('emp_pkey' => $arrDuplicateEmpKeys)));
        }
        $arrerrors = array();
        if ($errors != '' && $errors != '0') {
            $arrerrors = explode(',', urldecode($errors));
        }
        $this->set('arrerrors', $arrerrors);
        $this->set('arrDuplicateEmpList', $arrDuplicateEmpList);
    }

    public function sendpasswordemail($userid)
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->addAddress('info@mypayrollmaster.in');     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "New Employee Added To Payroll";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
	<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
    <tbody>
        <tr>
        	<td>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:100%!important">
                <tbody>
                	<tr>
                    	<td>
                			<table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                            <tbody>
                            	<tr>
                                    <td colspan="3" height="80" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="padding:0;margin:0;font-size:0;line-height:0">
                                        <table width="690" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td width="30"></td>
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.online/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="codexworld" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(65, 132, 243);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Employee Added to<font style="color:#fff;">' . $companyname . '</font> </h1>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.online is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.online/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
                                 	</tbody>
                                    </table>
                             	</td>
                   			</tr>
                            
                            <tr bgcolor="#ffffff">
                                <td width="30" bgcolor="#eeeeee"></td>
                                <td>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Your Login Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/anyone.png" alt="tool" width="120" height="120"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Emp Pkey</h3>
                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                <div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">'
                . $userid .
                '</div>
                                                <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/IaaS-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Organisation</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $companyname . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                  	</tbody>
                                    </table>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td>
                                            	<h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.online/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
                                      </tr></tbody></table></td>
                                <td width="30" bgcolor="#eeeeee"></td>
                            </tr>
                          	</tbody>
                            </table>
                  			<table align="center" width="750px" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:750px!important">
                            <tbody>
                            	<tr>
                                	<td>
                                        <table width="630" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                                        <tbody>
                                        	<tr><td colspan="2" height="30"></td></tr>
                                            <tr>
                                            	<td width="360" valign="top">
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
                                              	</td>
                                            </tr>
                                            <tr><td colspan="2" height="5"></td></tr>
                                           
                                      	</tbody>
                                        </table>
                                   	</td>
                  				</tr>
                          	</tbody>
                            </table>
                  		</td>
                	</tr>
              	</tbody>
                </table>
            </td>
		</tr>
 	</tbody>
    </table>
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    /*
     * Update salary structure for employee, for the current salary structure
     * On 20 Sep 2016
     */
    //arun 15-10-2016 based on ashoakn
    //    public function updateSalStructureDistributionFn($emp_fkey = 0) {
    //        if (!empty($emp_fkey)) {
    //            //Call sal_structure_distribution_fn starts
    //            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
    //            $arr_emp_structure_id = Set::extract('/EmployeeProfessionalDetails/.', $this->EmployeeProfessionalDetails->find('first', array(
    //                                'fields' => 'structure_id',
    //                                'conditions' => array('emp_fkey' => $emp_fkey)
    //                                    )
    //                            )
    //            );
    //            //debug($arr_emp_structure_id);
    //            $emp_structure_id = isset($arr_emp_structure_id[0]['structure_id']) ? $arr_emp_structure_id[0]['structure_id'] : '';
    //            if (!empty($emp_structure_id)) {
    //                $company_code = $this->Session->read('company_code');
    //                $login_user_id = $this->Session->read('login_user_id');
    //                $query_sal_structure_distribution_fn = "SELECT sal_structure_distribution_fn('$company_code', $emp_fkey, $emp_structure_id,'$login_user_id') AS resp";
    //                //echo $query_sal_structure_distribution_fn;
    //                $resp_sal_structure_distribution_fn = $this->EmployeeCTC->query($query_sal_structure_distribution_fn);
    //                $isSuccess = isset($resp_sal_structure_distribution_fn[0][0]['resp']) ? $resp_sal_structure_distribution_fn[0][0]['resp'] : 0;
    //                if ($isSuccess) {
    //                    //Need to call procedure to update salary structure vales for the employee, 
    //                    //by the current salary structure : PROGRESSING
    //                }
    //            }
    //            //Call sal_structure_distribution_fn ends
    //        }
    //    }
    //Ends



    public function downloadResume($emp_fkey = 0)
    {
        $this->autoRender = FALSE;

        $emp_pkey  = $emp_fkey;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_info = $this->EmployeeDetails->query("SELECT * FROM `comp_contact_info` WHERE `id` = 1 ");
        $company_code = $this->Session->read('company_code');

        require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

        // App::import('Vendor', 'TCPDF-main/examples', array('file' => 'tcpdf_include.php'));
        // $mail = new PHPMailer;

        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        define('COMPANY_NAME', $company_info['0']['comp_contact_info']['business_name']);
        define('COMPANY_MAIL', $company_info['0']['comp_contact_info']['email']);
        define('COMPANY_URLS', $company_info['0']['comp_contact_info']['email'] . "\n" . $company_info['0']['comp_contact_info']['website']);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetTitle($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        // $headerLogo = 'https://login.mypayrollmaster.online/newlogin/img/logo.png';
        $headerLogo = 'https://qaoci.mypayrollmaster.online/' . $company_info['0']['comp_contact_info']['logo'];

        // set default header data
        $pdf->SetHeaderData($headerLogo, '25', COMPANY_NAME, COMPANY_URLS, array(0, 64, 255), array(0, 64, 128));

        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)

        if (@file_exists(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"))) {
            require_once(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"));
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 14, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();


        // set text shadow effect
        $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));

        $prof_details = $this->EmployeeDetails->query("SELECT * FROM `employee_structure_vview` WHERE `emp_pkey` = $emp_pkey ");

        $arr_educations = $this->EmployeeDetails->query("SELECT * FROM `qualifcations` WHERE `emp_fkey` = $emp_pkey ");

        $arr_emp_proffs = $this->EmployeeDetails->query("SELECT * FROM `emp_proff` WHERE `emp_fkey` = $emp_pkey ");

        $arr_workExperiences = $this->EmployeeDetails->query("SELECT * FROM `history` WHERE `emp_fkey` = $emp_pkey ");

        $termination_details = $this->EmployeeDetails->query("SELECT * FROM `termination` WHERE `emp_fkey` = $emp_pkey");

        $arr_workHistory = $this->EmployeeDetails->query("SELECT * FROM `emp_config_history` WHERE `emp_fkey` = $emp_pkey");

        $user_image = $this->EmployeeDetails->query("SELECT avatar FROM `user_credentials` WHERE `emp_fkey` = $emp_pkey ");

        $image = '';
        $avtar = isset($user_image['0']['user_credentials']['avatar']) ? $user_image['0']['user_credentials']['avatar'] : '';
        // debug($avtar);exit;
        if ($avtar == 'null' || $avtar == null) {
            if ($arr_emp_details['EmployeeDetails']['classification'] == 'male') {
                $image = 'https://qaoci.mypayrollmaster.online/img/placeholdermen.jpeg';
            } else {
                $image = 'https://cdn4.vectorstock.com/i/thumb-large/52/83/default-placeholder-profile-icon-vector-14065283.jpg';
            }
        } else {
            $image = 'https://qaoci.mypayrollmaster.online/' . $avtar;
            //   $image = 'https://login.mypayrollmaster.online/'. $user_image['0']['user_credentials']['avatar'];
        }

        $html = '
    

    <table style="width: 98%;" cellspacing="0" cellpadding="0" border="0" "margin-top:10px">
        
        <tr>
        
          <td style="font-size:14px;"><font size="18"><strong>' . $arr_emp_details['EmployeeDetails']['first_name'] . ' ' . $arr_emp_details['EmployeeDetails']['last_name'] . '</strong></font><br>';
        if ($arr_emp_details['EmployeeDetails']['address'] != null || $arr_emp_details['EmployeeDetails']['address'] != '') {
            $html .= $arr_emp_details['EmployeeDetails']['address'] . '<br>' .
                $arr_emp_details['EmployeeDetails']['city'] . ', ' . $arr_emp_details['EmployeeDetails']['state'] . '<br>' .
                'PIN-' . $arr_emp_details['EmployeeDetails']['pincode'] . '<br>';
        }

        if ($arr_emp_details['EmployeeDetails']['mobile_no'] != null || $arr_emp_details['EmployeeDetails']['mobile_no'] != '') {
            $html .= '<span style="margin: 0;">' . $arr_emp_details['EmployeeDetails']['mobile_no'] . '</span><br>';
        }

        if ($arr_emp_details['EmployeeDetails']['email'] != null || $arr_emp_details['EmployeeDetails']['email'] != '') {
            $html .= '<span style="margin: 0;">' . $arr_emp_details['EmployeeDetails']['email'] . '</span><br>';
        }

        $html .= '
    </td>
    <td style="text-align: right; font-size: 12px; padding: 0 !important; margin: 0 !important; vertical-align: top; position: relative; right: -500px;">
        <img style="width: 103px !important; height: 120px !important; object-fit: contain !important; display: block; margin: -250px 0 0 0 !important; position: relative; top: -20px !important;" src="' . $image . '">
    </td>
</tr>
</table>
';



        $htmlHistories = '
        <br>
        <h4>Education</h4>
	 	<table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
	 		<tr>
	 			<th style="font-weight: bold; " >Course</th>
	 			<th style="font-weight: bold; " >University/college</th>
	 			<th style="font-weight: bold; " >Duration</th>
	 			<th style="font-weight: bold; " >Percentage/Marks</th>
	 		</tr>';

        foreach ($arr_educations as $key => $value) {
            $htmlHistories .= '<tr>
	 			<td>' . $value['qualifcations']['course'] . '</td>
	 			<td>' . $value['qualifcations']['university'] . '</td>
	 			<td>' . $value['qualifcations']['duration'] . '</td>
	 			<td>' . $value['qualifcations']['mark'] . '</td>
	 		</tr>';
        }

        $htmlHistories .= '</table>';

        $htmlExperiences = '
        <br>
        <h4>Experience</h4>

        <table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
        	<tr>
        		<th style="font-weight: bold; " >Company</th>
        		<th style="font-weight: bold; " >Department</th>
        		<th style="font-weight: bold; " >Designation</th>
        		<th style="font-weight: bold; " >From Date</th>
        		<th style="font-weight: bold; " >To Date</th>
        		<th style="font-weight: bold; " >CTC</th>
        	</tr>';

        foreach ($arr_workExperiences as $key => $value) {
            $htmlExperiences .= '
                <tr>
                    <td>' . $value['history']['company'] . '</td>
                    <td>' . $value['history']['department'] . '</td>
                    <td>' . $value['history']['designation'] . '</td>
                    <td>' . $value['history']['from_date'] . '</td>
                    <td>' . $value['history']['to_date'] . '</td>
                    <td>' . $value['history']['salary'] . '</td>
                </tr>';
        }

        $htmlExperiences .= '</table>';

        $htmlWorkhistory = '
        
        <h4>Work History</h4>

        <table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
           <tr>
             
              <th style="font-weight: bold; " >Date</th>
              <th style="font-weight: bold; " >Data Type</th>
              <th style="font-weight: bold; " >Changed To</th>
              <th style="font-weight: bold; " >Status</th>
              <th style="font-weight: bold; " >Approved By</th>
          </tr>';

        foreach ($arr_workHistory as $key => $value) {
            $datetime = $value['emp_config_history']['creation_date'];
            $date_obj = new DateTime($datetime);
            $formatted_date = $date_obj->format('d-m-Y');
            $time = $date_obj->format('H:i:s');
            $formatted_datetime = $formatted_date . ' &nbsp;&nbsp; ' . $time;
            $htmlWorkhistory .= '
                <tr>
                     <td>' . $formatted_datetime . '</td>
                     <td>' . $value['emp_config_history']['type'] . '</td>
                     <td>' . $value['emp_config_history']['day_time_desc'] . '</td>
                     <td>' . $value['emp_config_history']['status'] . '</td>
                     <td>' . $value['emp_config_history']['created_by'] . '</td>
                </tr>';
        }

        $htmlWorkhistory .= '</table>';

        // Check if the employee has resigned and add additional fields if true
        if (!empty($termination_details)) {
            $termination = $termination_details[0]['termination']; // Assuming you want the first record
            $htmlWorkhistory .= '
   
    <table style="width: 100%; font-size: 11px; line-height: 28px; text-align: center;" cellspacing="0" cellpadding="0" border="1">
        <tr>
            <td><b>Resignation Date:</b></td>
            <td>' . (isset($termination['submitted_date']) ? date('d-m-Y', strtotime($termination['submitted_date'])) : '') . '</td>
        </tr>
         <tr>
            <td><b>Reason:</b></td>
            <td>' . (isset($termination['Reason']) ? $termination['Reason'] : '') . '</td>
        </tr>
        <tr>
            <td><b>Relieving Date:</b></td>
            <td>' . (isset($termination['last_approved_working_date']) ? date('d-m-Y', strtotime($termination['last_approved_working_date'])) : '') . '</td>
        </tr>
        
        <tr>
            <td><b>Initiated By:</b></td>
            <td>' . (isset($termination['created_by']) ? $termination['created_by'] : '') . '</td>
        </tr>

        <tr>
            <td><b>Approved By:</b></td>
        </tr>
    </table>';
        }

        $tbl =
            '
      
        <div></div>
        
        <table style="width: 100%; font-size: 12px; border-color: #ccc; line-height: 28px; " bordercolor="#ccc" cellspacing="0" cellpadding="0" border="1">
           
             <tr>
                <td><b> Guardian Name: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['guradian'] . '</td>
                <td><b> Relation: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['relation_guardian'] . '</td>
            </tr>
            <tr>
                <td><b> Martial Status: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['maritual_status'] . '</td>
                <td><b> Education: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['education'] . '</td>
            </tr>
            <tr>
                <td><b> Gender: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['classification'] . ' </td>
                <td><b> Date Of Birth:</b></td> <td> ' . (isset($arr_emp_details['EmployeeDetails']['date_of_birth'])
                ? date('d-m-Y', strtotime($arr_emp_details['EmployeeDetails']['date_of_birth'])) : 'N/A') . '</td> 
            </tr>
            
            <tr>
    <td><b>' . (($company_code == 'DEMO' || $company_code == 'GLET') ? 'Aadhaar No:' : ' Aadhaar No:') . '</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['id_card'] . '</td>
    <td><b> Blood Group:</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['blood'] . '</td>
</tr>
           

            <tr>
                <td><b> ESI: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['esi'] . ' </td>
                <td><b> UAN: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['pf'] . '</td>
            </tr>
            
            <tr>
    <td><b>' . (($company_code == 'DEMO' || $company_code == 'GLET') ? 'PF:' : ' PF:') . '</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['company_pf'] . '</td>
    <td><b> PAN:</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['pan_no'] . '</td>
</tr>

        </table>

        <div></div>
        
          <table style="width: 100%; font-size: 12px; line-height: 28px; " cellspacing="0" cellpadding="0" border="1">
            <tr>
                
                <td style="padding: 4px; "><b> Joining Date: </b></td><td> ' . (isset($prof_details['0']['employee_structure_vview']['joining_date'])
                ? date('d-m-Y', strtotime($prof_details['0']['employee_structure_vview']['joining_date'])) : 'N/A') . '</td>
                
                     <td style="padding: 4px; "><b> Employee ID: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['employee_id'] . '</td>
            </tr>
            <tr>
                <td style="padding: 4px; "><b> Department: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['department'] . '</td>
                     <td style="padding: 4px; "><b> Designation: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['designation'] . '</td>
            </tr>
            <tr>
             
                  <td style="padding: 4px; "><b> Branch: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['branch'] . '</td>
                   <td style="padding: 4px; "><b> Employee Type: </b></td><td> ' . $arr_emp_proffs['0']['emp_proff']['emp_type'] . '</td>
<!--               <td style="padding: 4px; "><b> Site Customer: </b></td><td> Balachandran</td>-->
            </tr>
            <tr>
                <!--td style="padding: 4px; "><b> Phone Number: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['mobile_no'] . '</td--->
                <td style="padding: 4px; "><b> Grade: </b></td><td>  </td>
            </tr>
        </table>
         <table style="width: 100%; font-size: 12px; line-height: 28px; " cellspacing="0" cellpadding="0" border="1">
            <tr>
                <td><b> Bank Name: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['bank_name'] . ' </td>
                <td><b>  Branch: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['branch_name'] . ' </td>
            </tr>
            <tr>
                <td><b> IFSC: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['ifsc_code'] . ' </td>
                <td><b> Account Number: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['account_no'] . ' </td>
            </tr>
        </table>

        <br><br><br><br><br><br><br><br><br><br><br><br>
        ';

        // Print text using writeHTMLCell()
        $pdf->SetY(31);
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // $pdf->Image($image, 50, 10, 20, 40, '', '', '', false, 30, '', false, false, 0, false, false, false);


        $pdf->writeHTMLCell(0, 0, '', '', $tbl, 0, 1, 0, true, '', true);

        // $pdf->writeHTML($tbl, true, false, false, false, '');
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $htmlHistories, 0, 1, 0, true, '', true);

        $pdf->writeHTMLCell(0, 0, '', '', $htmlExperiences, 0, 1, 0, true, '', true);

        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $htmlWorkhistory, 0, 1, 0, true, '', true);



        $first_name = isset($arr_emp_details['EmployeeDetails']['first_name']) ? $arr_emp_details['EmployeeDetails']['first_name'] . ' ' . $arr_emp_details['EmployeeDetails']['last_name'] : 'example_001';

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        // $pdf->Output($first_name, 'I');

        $pdf->Output($first_name . '.pdf', 'D');

        //============================================================+
        // END OF FILE
        //============================================================+
    }

    // edited by anukrishnan_03-02-2025 open
    // public function incrimentdatapdf()
    // {
    //     $this->autoRender = false;

    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $company_info = $this->EmployeeDetails->query("SELECT * FROM `comp_contact_info` WHERE `id` = 1 ");

    //     require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

    //     $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    //     define('COMPANY_NAME', $company_info['0']['comp_contact_info']['business_name']);
    //     define('COMPANY_MAIL', $company_info['0']['comp_contact_info']['email']);
    //     define('COMPANY_URLS', $company_info['0']['comp_contact_info']['email'] . "\n" . $company_info['0']['comp_contact_info']['website']);

    //     $pdf->SetCreator(PDF_CREATOR);
    //     $pdf->SetAuthor($company_info['0']['comp_contact_info']['business_name']);
    //     $pdf->SetTitle($company_info['0']['comp_contact_info']['business_name']);
    //     $pdf->SetSubject('TCPDF Tutorial');
    //     $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
    //     // $headerLogo = 'https://login.mypayrollmaster.online/newlogin/img/logo.png';
    //     // $pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
    //     $headerLogo = 'https://qaoci.mypayrollmaster.online/' . $company_info['0']['comp_contact_info']['logo'];

    //     $pdf->SetHeaderData($headerLogo, '25', COMPANY_NAME, COMPANY_URLS, array(0, 64, 255), array(0, 64, 128));

    //     $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

    //     $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    //     $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    //     $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //     $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    //     $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //     $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    //     $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    //     $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    //     if (@file_exists(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"))) {
    //         require_once(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"));
    //         $pdf->setLanguageArray($l);
    //     }

    //     $pdf->setFontSubsetting(true);

    //     $pdf->SetFont('dejavusans', '', 14, '', true);

    //     $pdf->AddPage();

    //     $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

    //     $fromDate = date("Y-m-01");
    //     $nextMonth = date("Y-m-d", strtotime("+1 month"));
    //     $entDate = date("Y-m-t", strtotime($nextMonth));

    //     $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
    //     $incrementdata = $this->EmployeeCTC->query("SELECT 
    //             e.emp_fkey, 
    //             ei.EmpName, 
    //             ei.employee_id, 
    //             MAX(e.next_increment_date) AS next_increment_date
    //         FROM 
    //             emp_ctc_upload e
    //         JOIN 
    //             employee_info ei ON e.emp_fkey = ei.emp_pkey
    //         WHERE 
    //             e.next_increment_date BETWEEN '$fromDate' AND '$entDate'
    //         GROUP BY 
    //             e.emp_fkey, ei.EmpName, ei.employee_id;
    //     ");

    //     $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
    //     $notify = $this->EmployeeMenu->query(
    //         "select distinct site.site_id,working_day_time_procedures.day_time_desc from site_transactions "
    //             . "left join site on(site.site_pkey = site_transactions.site_fkey) "
    //             . "left join working_day_time_procedures on(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) "
    //             . "where DATEDIFF(site_transactions.end_date_effective,now()) > 0 and DATEDIFF(site_transactions.end_date_effective,now()) < 31 "
    //             . "and site_pkey is not null and site.status = 1 and site_transactions.status = 1"
    //     );

    //     if (!empty($incrementdata)) {
    //         $html = '<h2 style="text-align:center;">Employee Increment Report</h2>';
    //         $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">
    //                     <thead>
    //                         <tr style="background-color:#f2f2f2;">
    //                             <th>Employee ID</th>
    //                             <th>Employee Name</th>
    //                             <th>Next Increment Date</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>';

    //         foreach ($incrementdata as $data) {
    //             $html .= '<tr>
    //                         <td>' . $data['ei']['employee_id'] . '</td>
    //                         <td>' . $data['ei']['EmpName'] . '</td>
    //                         <td>' . date('d-m-Y', strtotime($data[0]['next_increment_date'])) . '</td>
    //                     </tr>';
    //         }

    //         $html .= '</tbody></table>';
    //     }

    //     if (!empty($notify)) {
    //         $html .= '<h2 style="text-align:center;">Site Notifications</h2>';
    //         $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">
    //                     <thead>
    //                         <tr style="background-color:#f2f2f2;">
    //                             <th>Site Id</th>
    //                             <th>Site Name</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>';

    //         foreach ($notify as $data) {
    //             $site_id = $data['site']['site_id'];
    //             $day_time_desc = $data['working_day_time_procedures']['day_time_desc'];

    //             $html .= '<tr>
    //                         <td>' . htmlspecialchars($site_id) . '</td>
    //                         <td>' . htmlspecialchars($day_time_desc) . '</td>
    //                     </tr>';
    //         }

    //         $html .= '</tbody></table>';
    //     }

    //     $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    //     $pdf->writeHTML($html, true, false, true, false, '');

    //     $fileName = 'Employee_Increment_Report_' . date('Y-m-d') . '.pdf';
    //     $pdf->Output($fileName, 'D');
    // }
    // edited by anukrishnan_03-02-2025 close
    // edited by anukrishnan_04-02-2025 open
    public function employeeincrement()
    {
        $arr_increment = array(
            'increment_history' => 'Increment History'
        );
        $this->set('arr_increment', $arr_increment);
    }

    public function changeIncrementType($type = '')
    {
        $this->autoRender = false;

        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'increment_history':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_increment', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type), 'order' => 'reportcriteria_desc'))));
                    break;
                default:
                    echo "No criterias found";
                    break;
            }
            $this->render('showreport');
        } else {
            echo "No criterias found";
        }
    }

    private function _modelExists($modelName)
    {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }

    public function loadcriteriaitems($index, $str_criteria = '')
    {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Units') ? 'Branches' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        $arr_criteriaItems = array();

        if (isset($model) && $model != '') {
            // Set the datasource configuration for the model
            $this->{$model}->useDbConfig = $this->Session->read('ds');

            // Define conditions based on the model
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } else {
                $conditions = array("status" => 1);
            }

            // Special handling for the Types model
            if ($model == 'Types') {
                // Assuming Types is a static array or predefined data
                // $arr_criteriaItemsDB = array('Birthday', 'Anniversary', 'Probation');
                $arr_criteriaItemsDB = array('Anniversary', 'Birthday', 'Probation');
            } else {
                // Fetch data from the database for other models
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            }

            // Process the fetched data
            $key = 0;
            switch ($model) {
                case 'Units':
                    foreach ($arr_criteriaItemsDB as $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;

                case 'EmployeeDetails':
                    // Join and fetch employee details
                    $fields = 'emp_pkey, status, EmployeeProfessionalDetails.emp_company_id, 
                               CONCAT(first_name, " ", IFNULL(last_name, "."), " - ", EmployeeProfessionalDetails.emp_company_id) as name, 
                               EmployeeProfessionalDetails.designation, EmployeeProfessionalDetails.joining_date, mobile_no';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );
                    $conditions = array("status" => 1);
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions = array("status IN (1, 2)");
                    }
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        "order" => array("EmployeeDetails.first_name" => "ASC")
                    ));
                    foreach ($arr_emp as $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;

                case 'Types':
                    // Process the static array for Types
                    foreach ($arr_criteriaItemsDB as $value) {
                        $arr_criteriaItems[$key]['key'] = strtolower($value);
                        $arr_criteriaItems[$key]['text'] = $value;
                        $key++;
                    }
                    break;

                default:
                    foreach ($arr_criteriaItemsDB as $value) {
                        $arr_criteriaItems[$key]['key'] = $value[$model]['id'];
                        $arr_criteriaItems[$key]['text'] = $value[$model]['name'];
                        $key++;
                    }
                    break;
            }
        }

        // Output the results
        echo json_encode($arr_criteriaItems);
    }

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        switch ($type) {
            case 'increment_history':
                $this->generateemployeincrement($mode);
                break;
            default:
                return false;
                break;
        }
    }

    public function generateemployeincrement($mode)
    {
        $arr_form_data = $_REQUEST;
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $reportFrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : null;
        $reportFromFormatted = $reportFrom . '-01';
        $reportYear = date('Y', strtotime($reportFrom));
        $reportMonth = date('m', strtotime($reportFrom));
        $mname = date('F', mktime(0, 0, 0, $reportMonth, 10));

        $employeeIds = isset($arr_form_data['EmployeeDetails']) ? $arr_form_data['EmployeeDetails'] : [];
        $employeeIdsList = !empty($employeeIds) ? "'" . implode("','", $employeeIds) . "'" : "''";

        $branchs = isset($arr_form_data['Units']) ? $arr_form_data['Units'] : [];
        $branchCondition = "";

        if (!empty($branchs)) {
            $branchsList = "'" . implode("','", $branchs) . "'";
            $branchCondition = "AND e.branch_code IN ($branchsList)";
        }

        $resigned = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : 0;
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND e.emp_status = 1";
        } else {
            $condition = " AND e.emp_status IN(1, 2)";
        }
        //edited by athira on 13-06-2025
        if (!empty($employeeIds)) {
            $incrementquery = $this->EmployeeCTC->query("
                SELECT c.*, e.EmpName, e.employee_id, e.joining_date, e.branch, u.user_id,t.last_approved_working_date,ROUND(c.emp_anual_ctc / 12, 0) AS new_gross_salary
                FROM emp_ctc_upload c
                JOIN employee_info e ON e.emp_pkey = c.emp_fkey
                JOIN user_credentials u ON u.emp_fkey = e.emp_pkey
                LEFT JOIN termination t ON t.emp_fkey = e.emp_pkey
                WHERE YEAR(next_increment_date) = '$reportYear'
                AND MONTH(next_increment_date) = '$reportMonth'
                AND c.emp_fkey IN ($employeeIdsList)
                AND c.created_date = (
                    SELECT MAX(created_date)
                    FROM emp_ctc_upload
                    WHERE emp_fkey = c.emp_fkey
                    AND YEAR(next_increment_date) = '$reportYear'
                    AND MONTH(next_increment_date) = '$reportMonth'
                )
                $condition
                ORDER BY e.EmpName ASC
            ");
        } else {
            $incrementquery = $this->EmployeeCTC->query("
                SELECT c.*, e.EmpName, e.employee_id, e.joining_date, e.branch, u.user_id,t.last_approved_working_date,ROUND(c.emp_anual_ctc / 12, 0) AS new_gross_salary
                FROM emp_ctc_upload c
                JOIN employee_info e ON e.emp_pkey = c.emp_fkey
                JOIN user_credentials u ON u.emp_fkey = e.emp_pkey
                LEFT JOIN termination t ON t.emp_fkey = e.emp_pkey
                WHERE YEAR(next_increment_date) = '$reportYear'
                AND MONTH(next_increment_date) = '$reportMonth'
                AND c.created_date = (
                    SELECT MAX(created_date)
                    FROM emp_ctc_upload
                    WHERE emp_fkey = c.emp_fkey
                    AND YEAR(next_increment_date) = '$reportYear'
                    AND MONTH(next_increment_date) = '$reportMonth'
                )
                $branchCondition
                $condition
                ORDER BY e.branch ASC, e.EmpName ASC
            ");
        }
        //end


        $processedData = [];
        $slNo = 1;
        foreach ($incrementquery as $row) {
            $processedData[] = [
                'SlNo'           => $slNo++,
                'EmployeeID'     => isset($row['e']['employee_id']) ? $row['e']['employee_id'] : '',
                'EmployeeName'   => isset($row['e']['EmpName']) ? trim($row['e']['EmpName']) : '',
                'JoiningDate'    => DateTime::createFromFormat('Y-m-d', $row['e']['joining_date'])->format('d-m-Y'),
                'IncrementDate'  => DateTime::createFromFormat('Y-m-d', $row['c']['next_increment_date'])->format('d-m-Y'),
                //edited by athira on 13-06-2025
                'TerminationDate' => !empty($row['t']['last_approved_working_date']) && ($date = DateTime::createFromFormat('Y-m-d', $row['t']['last_approved_working_date'])) ? $date->format('d-m-Y') : '',
                'NewGrossSalary' => isset($row['0']['new_gross_salary']) ? $row['0']['new_gross_salary'] : '',
                //end
                'create_date' => date('d-m-Y', strtotime($row['c']['created_date'])),
                'Amount'         => isset($row['c']['emp_anual_ctc']) ? $row['c']['emp_anual_ctc'] : '',
                'Branch'         => isset($row['c']['branch']) ? $row['c']['branch'] : '',
                'Department'     => isset($row['c']['department']) ? $row['c']['department'] : '',
                'Designation'    => isset($row['c']['designation']) ? $row['c']['designation'] : '',
                'branch_head'    => isset($row['e']['branch']) ? $row['e']['branch'] : '',
                'user_id'    => isset($row['u']['user_id']) ? $row['u']['user_id'] : '',
            ];
        }

        $this->set('selectCriteria1', $arr_form_data['select-criteria1']);
        $this->set('processedData', $processedData);
        $this->set('mname',  $mname);
        $this->set('yname',  $reportYear);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);


        switch ($mode) {
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Employee_Increment_" . $reportMonth . "-" . $reportYear . ".xlsx" : "Events" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Attendance Register By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $table_count = 0;
                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Increment- " . $mname . "  "  . $reportYear);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $worksheet->getStyle('A2')->getFont()->setBold(true);
                $worksheet->getStyle('A2')->getFont()->setSize(13);
                $worksheet->mergeCells('A2:K2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                if ($processedData) {
                    $headerStyleArray = [
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'D9E1F2']
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ],
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ];

                    //edited by athira on 13-06-2025
                    $worksheet->getStyle('A3:M3')->applyFromArray($headerStyleArray);
                    //end

                    $worksheet->setCellValueByColumnAndRow(0, 3, "Sl No");
                    $worksheet->setCellValueByColumnAndRow(1, 3, "Employee ID");
                    $worksheet->setCellValueByColumnAndRow(2, 3, "User ID");
                    $worksheet->setCellValueByColumnAndRow(3, 3, "Employee Name");
                    $worksheet->setCellValueByColumnAndRow(4, 3, "Branch");
                    $worksheet->setCellValueByColumnAndRow(5, 3, "Department");
                    $worksheet->setCellValueByColumnAndRow(6, 3, "Designation");
                    $worksheet->setCellValueByColumnAndRow(7, 3, "Joining Date");
                    //edited by athira on 13-06-2025
                    $worksheet->setCellValueByColumnAndRow(8, 3, "Termination Date");
                    //end
                    $worksheet->setCellValueByColumnAndRow(9, 3, "Amount");
                    $worksheet->setCellValueByColumnAndRow(10, 3, "Increment Date");
                    $worksheet->setCellValueByColumnAndRow(11, 3, "Created Date");
                    //edited by athira on 13-06-2025
                    $worksheet->setCellValueByColumnAndRow(12, 3, "New Gross Salary");
                    //end



                    $rowIndex = 4;
                    foreach ($processedData as $data) {
                        foreach (range(0, 9) as $colIndex) {
                            $worksheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
                        }
                        $worksheet->setCellValueByColumnAndRow(0, $rowIndex, $data['SlNo']);
                        $worksheet->getStyleByColumnAndRow(0, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(1, $rowIndex, $data['EmployeeID']);
                        $worksheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(2, $rowIndex, $data['user_id']);
                        $worksheet->getStyleByColumnAndRow(2, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(3, $rowIndex, $data['EmployeeName']);
                        $worksheet->getStyleByColumnAndRow(3, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(4, $rowIndex, $data['Branch']);
                        $worksheet->getStyleByColumnAndRow(4, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(5, $rowIndex, $data['Department']);
                        $worksheet->getStyleByColumnAndRow(5, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(6, $rowIndex, $data['Designation']);
                        $worksheet->getStyleByColumnAndRow(6, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(7, $rowIndex, $data['JoiningDate']);
                        $worksheet->getStyleByColumnAndRow(7, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        //edited by athira on 13-06-2025
                        $worksheet->setCellValueByColumnAndRow(8, $rowIndex, $data['TerminationDate']);
                        $worksheet->getStyleByColumnAndRow(8, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        //end
                        $worksheet->setCellValueByColumnAndRow(9, $rowIndex, $data['Amount']);
                        $worksheet->getStyleByColumnAndRow(9, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(10, $rowIndex, $data['IncrementDate']);
                        $worksheet->getStyleByColumnAndRow(10, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(11, $rowIndex, $data['create_date']);
                        $worksheet->getStyleByColumnAndRow(11, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        //edited by athira on 13-06-2025
                        $worksheet->setCellValueByColumnAndRow(12, $rowIndex, $data['NewGrossSalary']);
                        $worksheet->getStyleByColumnAndRow(12, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        //end
                        $rowIndex++;
                        $table_count++;
                    }
                }
                $worksheet->getColumnDimension('K')->setWidth(20);
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                //edited by athira on 13-06-2025
                $worksheet->getColumnDimension('L')->setWidth(20);
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $worksheet->getColumnDimension('M')->setWidth(20);
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                //end
                if ($table_count == 0) {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $worksheet->mergeCells('A3:J3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    );
                    //edited by athira on 13-06-2025
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'M3')->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'M' . ($rowIndex - 1))->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                }
                //end

                $objPHPExcel->getActiveSheet()->setTitle('Employee_Increment');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                exit;
            default:
                $this->set('mode', '');
                $this->render('employeeincrementview');
                break;
        }
    }

    // edited by anukrishnan_04-02-2025 close
    // edited by anukrishnan_17-02-2025 open
    public function currentctctake()
    {
        $this->autoRender = false;
        $empId = $this->request->data['emp_fkey'];
        // debug($empId);
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $emp_ctc = $this->EmployeeCTC->query("
                    SELECT emp_ctc_upload.next_increment_date, emp_ctc_upload.created_date,emp_ctc_upload.emp_anual_ctc
                    FROM emp_ctc_upload 
                    WHERE emp_ctc_upload.emp_fkey = '$empId'
                    AND emp_ctc_upload.status = 1
                    ORDER BY created_date DESC
                    LIMIT 1;
                ");

        $emp_ctc_salary = $this->EmployeeCTC->query("
                    SELECT emp_ctc_upload.emp_anual_ctc, emp_ctc_upload.created_date,emp_ctc_upload.emp_anual_ctc
                    FROM emp_ctc_upload 
                    WHERE emp_ctc_upload.emp_fkey = '$empId'
                    AND emp_ctc_upload.status = 1
                    AND emp_ctc_upload.next_increment_date = (
                        SELECT MAX(next_increment_date) 
                        FROM emp_ctc_upload 
                        WHERE emp_fkey = '$empId'
                        AND status = 1
                        AND next_increment_date < CURDATE()
                    )
                    LIMIT 1
                ");

        $response = [];
        if (!empty($emp_ctc_salary)) {
            $response['emp_anual_ctc'] = $emp_ctc_salary[0]['emp_ctc_upload']['emp_anual_ctc'];
            $revision_date = new DateTime($emp_ctc_salary[0]['emp_ctc_upload']['created_date']);
            $formattedrevisionDate = $revision_date->format('d-m-Y');
            $response['prevision_revision_date'] = $formattedrevisionDate;
        } else {

            if (!empty($emp_ctc)) {
                $response['emp_anual_ctc'] = $emp_ctc[0]['emp_ctc_upload']['emp_anual_ctc'];
                $response['prevision_revision_date'] = 'No Data Found';
            }
        }

        if (!empty($emp_ctc)) {
            $nextIncrementDate = $emp_ctc[0]['emp_ctc_upload']['next_increment_date'];
            if (!empty($nextIncrementDate) && $nextIncrementDate !== '0000-00-00') {
                $createdDate = new DateTime($nextIncrementDate);
                $formattedDate = $createdDate->format('d-m-Y');
            } else {
                $formattedDate = 'No Data Found';
            }
            $response['previous_increment_date'] = $formattedDate;
        } else {
            $response['previous_increment_date'] = 'No Data Found';
        }

        if (empty($response)) {
            $response['error'] = 'No Data Found';
        }

        echo json_encode($response);


        // if (!empty($emp_ctc)) {
        //     var_dump($emp_ctc_salary); exit;
        //     $revision_date =  new DateTime($emp_ctc[0]['emp_ctc_upload']['created_date']);
        //     $formattedrevisionDate = $revision_date->format('d-m-Y');

        //     $nextIncrementDate = $emp_ctc[0]['emp_ctc_upload']['next_increment_date'];
        //     if (!empty($nextIncrementDate) && $nextIncrementDate !== '0000-00-00') {
        //         $createdDate = new DateTime($nextIncrementDate);
        //         $formattedDate = $createdDate->format('d-m-Y');
        //     } else {
        //         $formattedDate = 'no increment date added';
        //     }

        //     echo json_encode([
        //         'emp_anual_ctc' => $emp_ctc_salary[0]['emp_ctc_upload']['emp_anual_ctc'],
        //         'previous_increment_date'   => $formattedDate,
        //         'prevision_revision_date'   => $formattedrevisionDate
        //     ]);
        // } else {
        //     echo json_encode(['error' => 'No CTC data found']);
        // }

    }
    // edted by anukrishnan_17-02-2025 close
    //Edited by Akshay on 6-4-2024
    public function changeBranchAr()
    {
        $this->autoRender = FALSE;
        $branch_code = $_POST['branch'];
        $pkey = $_POST['pkey'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_ar_update = $this->EmployeeDetails->query("UPDATE attendance_register ar
                LEFT JOIN payroll_master pm ON pm.emp_fkey = ar.emp_fkey AND pm.month_year = ar.month_year
                SET ar.branch_code = '$branch_code'
                WHERE ar.emp_fkey = '$pkey'
                    AND ar.isdelete = 'N'
                    AND (pm.action IS NULL OR pm.action NOT IN ('Processed', 'Approved'));        
                ");
        //edited by athira on 04-06-2025
        $arr_ar_update2 = $this->EmployeeDetails->query("UPDATE attendance_register_calendar arc
                LEFT JOIN payroll_master pm ON pm.emp_fkey = arc.emp_fkey AND pm.month_year = arc.month_year
                SET arc.branch_code = '$branch_code'
                WHERE arc.emp_fkey = '$pkey'
                    AND arc.isdelete = 'N'
                    AND (pm.action IS NULL OR pm.action NOT IN ('Processed', 'Approved'));        
                ");
        //end
        $num_rows = $this->EmployeeDetails->query("SELECT ROW_COUNT() AS num_rows_affected")[0][0]['num_rows_affected'];
        if ($num_rows > 0) {
            $changed_rows = true;
            $arr_pm_delete = $this->EmployeeDetails->query("DELETE FROM payroll_master
                                                            WHERE branch_code != '$branch_code'
                                                            AND (action IS NULL OR action = '')
                                                            AND emp_fkey = '$pkey';");
        } else {
            $changed_rows = false;
        }

        return json_encode(array('success' => TRUE, 'error' => '', 'message' => "Employee Added Successfully ", "changed_rows" => $changed_rows));
    }

    //Edited by Akshay on 16-5-2024
    public function confirmation($month_year = '', $branch = '', $pkey = '')
    {
        $this->set('month_year', $month_year);
        $this->set('branch', $branch);
        $this->set('pkey', $pkey);
    }

    public function saveConfirmation()
    {
        $this->autoRender = FALSE;
        return json_encode(array('success' => true, 'confirmation' => $_POST['confirmation']));
    }
    //End

    // Edited by Akshay on 5-4-2025
    public function salaryIncrementForm()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeCTC->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 order by first_name ASC');
        $this->set("arr_employees", $arr_employees);

        $arr_salary = $this->EmployeeCTC->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $this->render('salary_increment');
    }

    public function getSalaryStructure($emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $this->response->type('json');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $arr_sal_structure = $this->EmployeeCTC->query("SELECT DISTINCT emp_structure_id
                                                            FROM emp_salary_structure
                                                            WHERE emp_fkey = '$emp_pkey'
                                                            AND end_date_effective IS NULL
                                                            LIMIT 1;
                                                            ");
        $structure_id = isset($arr_sal_structure[0]['emp_salary_structure']['emp_structure_id']) ? $arr_sal_structure[0]['emp_salary_structure']['emp_structure_id'] : '';
        // Gross Salary Components
        $arr_emp_sal_structure = $this->EmployeeCTC->query("SELECT emp_salary_structure.salary_head_item_fkey, emp_salary_structure.salary_head_item_desc, emp_salary_structure.structure_det_value 
                                                                FROM emp_salary_structure
                                                                LEFT JOIN salary_head_items 
                                                                    ON emp_salary_structure.salary_head_item_fkey = salary_head_items.salary_head_item_pkey
                                                                LEFT JOIN salary_heads 
                                                                    ON salary_head_items.head_fkey = salary_heads.head_pkey
                                                                WHERE emp_fkey = $emp_pkey
                                                                AND TRIM(emp_salary_structure.head_operator) = 'Addition' 
                                                                AND TRIM(emp_salary_structure.item_part) = 'Direct' 
                                                                AND emp_salary_structure.end_date_effective IS NULL
                                                                AND salary_heads.head_pkey = 1;
                                                                ");
        $result = [];
        foreach ($arr_emp_sal_structure as $item) {
            $result[] = [
                'key' => ($item['emp_salary_structure']['salary_head_item_fkey']) ? $item['emp_salary_structure']['salary_head_item_fkey'] : '',
                'desc' => ($item['emp_salary_structure']['salary_head_item_desc']) ? $item['emp_salary_structure']['salary_head_item_desc'] : '',
                'value' => $item['emp_salary_structure']['structure_det_value'] ? $item['emp_salary_structure']['structure_det_value'] : ''
            ];
        }



        $arr_emp_sal_structure = $this->EmployeeCTC->query("SELECT emp_salary_structure.salary_head_item_fkey, emp_salary_structure.salary_head_item_desc, emp_salary_structure.structure_det_value 
                                                                FROM emp_salary_structure
                                                                LEFT JOIN salary_head_items 
                                                                    ON emp_salary_structure.salary_head_item_fkey = salary_head_items.salary_head_item_pkey
                                                                LEFT JOIN salary_heads 
                                                                    ON salary_head_items.head_fkey = salary_heads.head_pkey
                                                                WHERE emp_fkey = $emp_pkey
                                                                AND TRIM(emp_salary_structure.head_operator) = 'Addition' 
                                                                AND TRIM(emp_salary_structure.item_part) = 'Indirect' 
                                                                AND emp_salary_structure.end_date_effective IS NULL
                                                                AND salary_heads.head_pkey != 1
                                                                AND salary_head_item_fkey IN
                                                                    (
                                                                    select salary_head_item_pkey from salary_head_items where head_fkey in (4,10)
                                                                    )
                                                                ;
                                                                ");

        $result_indirect = [];
        foreach ($arr_emp_sal_structure as $item) {
            $result_indirect[] = [
                'key' => ($item['emp_salary_structure']['salary_head_item_fkey']) ? $item['emp_salary_structure']['salary_head_item_fkey'] : '',
                'desc' => ($item['emp_salary_structure']['salary_head_item_desc']) ? $item['emp_salary_structure']['salary_head_item_desc'] : '',
                'value' => $item['emp_salary_structure']['structure_det_value'] ? $item['emp_salary_structure']['structure_det_value'] : ''
            ];
        }


        $arr_emp_info = $this->EmployeeCTC->query("SELECT emp_pkey, branch, designation, department, joining_date 
                                                        FROM employee_info 
                                                        WHERE emp_pkey = $emp_pkey;
                                                        ");

        $emp = [];
        foreach ($arr_emp_info as $emp_info) {
            foreach ($emp_info as $key => $info) {
                $emp[] = $info;
            }
        }

        $arr_ctc_result = $this->EmployeeCTC->query("SELECT emp_anual_ctc 
                                                            FROM emp_ctc_transaction 
                                                            WHERE emp_fkey = $emp_pkey 
                                                            AND end_date_effective IS NOT NULL 
                                                            ORDER BY end_date_effective DESC 
                                                            LIMIT 1;
                                                        ");
        $ctc = isset($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc']) ? ($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc'] / 12) : '';

        echo json_encode(['success' => true, 'structure_id' => $structure_id, 'structure' => $result, 'structure_indirect' => $result_indirect, 'emp' => $emp[0], 'ctc' => $ctc]);
        return;
    }

    public function calcSalaryStructure()
    {
        $this->autoRender = false;
        $this->response->type('json'); // Set response content type as JSON

        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $empFkey      = isset($_POST['emp_fkey'])       ? (int)$_POST['emp_fkey']       : 0;
        $monthlyGross = isset($_POST['monthly_gross'])  ? (float)$_POST['monthly_gross'] : 0;
        $montlyCtc = isset($_POST['monthly_ctc'])  ? (float)$_POST['monthly_ctc'] : 0;

        $arr_emp_structure_id = $this->EmployeeCTC->query("
            SELECT DISTINCT emp_structure_id
            FROM emp_salary_structure
            WHERE emp_fkey = '$empFkey'
            AND end_date_effective IS NULL;
        ");

        $emp_structure_id = isset($arr_emp_structure_id[0]['emp_salary_structure']['emp_structure_id'])
            ? $arr_emp_structure_id[0]['emp_salary_structure']['emp_structure_id']
            : '';

        if ($emp_structure_id != '') {
            $arr_result = $this->EmployeeCTC->query("CALL calculate_emp_salary_breakup('$empFkey', $emp_structure_id, '$monthlyGross');");

            if (!empty($arr_result)) {
                // Convert and return JSON response
                echo json_encode($arr_result, JSON_PRETTY_PRINT);
                return;
            }
        }

        // If no result or emp_structure_id is empty, return empty JSON or error
        echo json_encode([
            'status' => 'error',
            'message' => 'No data found or invalid employee structure.'
        ]);
    }

    public function alterSalaryStructure()
    {
        $this->autoRender = FALSE;
        $this->response->type('json');
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $company = $this->Session->read('company_code');
        $user_ids = $this->Session->read('login_user_id');
        $emp = $arr_form_data['emp_fkey'];
        $fetch_proff = $this->EmployeeConfig->query("select * from emp_proff where emp_fkey = '$emp' ");
        $salary_id = isset($fetch_proff['0']['emp_proff']['structure_id']) ? $fetch_proff['0']['emp_proff']['structure_id'] : 0;

        if (isset($arr_form_data['structure_id']) && $arr_form_data['structure_id'] != $salary_id) {

            $condition2['type'] = 'SALARY';
            $condition2['emp_fkey'] = $arr_form_data['emp_fkey'];

            $edit_salary = TRUE;

            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $salary_id = $arr_form_data['structure_id'];
            //added by megha salary allocation 1
            $resp = 0;
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $user_ids = $this->Session->read('login_user_id');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            try {
                $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                //debug($proc);exit;
                //employee condition added by megha on 5/03/2020
                $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                    "all",
                    array(
                        'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                        'conditions' => array(
                            'emp_structure_id' => $salary_id,
                            'remarks IS NOT NULL',
                            'emp_fkey' => $emp,
                            'end_date_effective is null'
                        )
                    )
                );

                foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                    $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                    $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                    $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';

                    $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';
                    // $head_type=isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_type'])?$row_formulae_from_remarks['EmpSalarySlip']['head_type']:'';
                    if (!empty($formula_from_remarks)) {
                        eval('$salary_amount = ' . $formula_from_remarks . ';');

                        if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                            if ($head_operator == 'Deduction') {
                                $salary_amount = ceil($salary_amount); //Edited by Akshay on 30-4-2024
                            } else {
                                $salary_amount = round($salary_amount); //Edited by Akshay on 30-4-2024
                            }


                            if ($head_operator == 'Deduction') {
                                $salary_amount *= -1;
                            }


                            $arr_emp_salary_slip_data = array(
                                'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                            );
                        } else {
                            $salary_amount = round($salary_amount);
                            if ($head_operator == 'Deduction') {
                                $salary_amount *= -1;
                            }
                            $arr_emp_salary_slip_data = array(
                                'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                            );
                        }

                        $this->EmployeeSalaryStructure->updateAll(
                            $arr_emp_salary_slip_data,
                            array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                        );
                    }
                }


                $resp = 1;
            } catch (Exception $ex) {
                debug($ex);
                $resp = 0;
            }
            //end salary allocation
            try {
                $user_ids = $this->Session->read('login_user_id');

                //debug($result);
                $data['type'] = 'SALARY';
                $data['policy_id'] = $arr_form_data['structure_id'];
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $user_ids . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition2);

                $this->EmployeeConfig->saveAll($data);
                //added by megha sallary allocation 2
                $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save salary policy "));
            }

            if ($resp == 1) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Salary structure updated successfully.'
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Failed to update salary structure.'
                ));
            }
            exit;
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Structure is same as the current structure.'
            ));
        }
    }

    public function onEffectiveDateChange()
    {
        $this->autoRender = FALSE;
        // $this->response->type('json');

        try {
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $empFkey = isset($_POST['empPkey']) ? (int)$_POST['empPkey'] : 0;
            $start_date_effective = isset($_POST['start_date_effective']) ? $_POST['start_date_effective'] : 0;
            if (!empty($start_date_effective)) {
                $date = DateTime::createFromFormat('d-m-Y', $start_date_effective);
                $month = $date->format('Y-m');

                $arr_processed = $this->EmployeeCTC->query(
                    "SELECT COUNT(*) as count FROM payroll_master
                                            WHERE emp_fkey = $empFkey
                                            AND month_year = '$month'
                                            AND action = 'Approved';"
                );
                $is_processed = $arr_processed[0][0]['count'];

                if ($is_processed > 0) {
                    echo json_encode(['status' => 'success', 'is_processed' => true,  'message' => 'Payroll already approved for this month. This will be included in arrear']);
                } else {
                    echo json_encode(['status' => 'success', 'is_processed' => false, 'message' => 'Payroll not yet processed for this month.']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }


    public function saveIncrement()
    {
        $this->autoRender = FALSE;

        try {
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            $this->SalaryIncrement->useDbConfig = $this->Session->read('ds');
            $this->SalaryIncrementDetails->useDbConfig = $this->Session->read('ds');

            $arr_form_data = $_POST;
            $emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
            $salary_structure = isset($arr_form_data['salary_structure']) ? $arr_form_data['salary_structure'] : '';
            $user_id = $this->Session->read("login_user_id");
            $arr_form_data['created_by'] = $user_id;

            // Convert dd-mm-yyyy to Y-m-d
            $arr_form_data['start_date_effective'] = date('Y-m-d', strtotime($arr_form_data['start_date_effective']));
            $arr_form_data['next_increment_date']  = date('Y-m-d', strtotime($arr_form_data['next_increment_date']));

            // Convert September-2025 to Y-m-1
            $arr_form_data['pay_out_month'] = date('Y-m-1', strtotime('01-' . $arr_form_data['pay_out_month']));

            // debug($arr_form_data);
            $total_ctc = 0;
            foreach ($arr_form_data as $key => $value) {
                if ((strpos($key, 'new_value_') === 0 || strpos($key, 'indirect_new_value_') === 0) && is_numeric($value)) {
                    $total_ctc += round((float)$value);
                }
            }

            // $arr_form_data['emp_monthly_ctc'] = $total_ctc;
            // $arr_form_data['emp_anual_ctc'] = $total_ctc * 12;

            $arr_branch = $this->EmployeeCTC->query("SELECT branch_code FROM emp_details ed
                                                        WHERE emp_pkey = '$emp_fkey';
                                                        ");
            $branch = isset($arr_branch[0]['ed']['branch_code']) ? $arr_branch[0]['ed']['branch_code'] : '';
            $month = isset($arr_form_data['start_date_effective']) ? $arr_form_data['start_date_effective'] : 0;
            if ($month != 0) {
                $month = date('Y-m', strtotime($month));
            } else {
                $month = 0;
            }

            $result = $this->EmployeeCTC->save($arr_form_data);

            // Edited by Akshay on 23-4-2025
            $arr_ctc_upload_pkey = $this->EmployeeCTC->query("SELECT emp_ctc_upload_pkey
                                            FROM emp_ctc_upload
                                            ORDER BY emp_ctc_upload_pkey DESC
                                            LIMIT 1;
                                            ");
            $ctc_upload_pkey = $arr_ctc_upload_pkey['0']['emp_ctc_upload']['emp_ctc_upload_pkey'];
            $arr_form_data['emp_ctc_upload_fkey'] = $ctc_upload_pkey;

            $this->SalaryIncrement->save($arr_form_data);
            // End

            $arr_salary_increment_pkey = $this->SalaryIncrement->query("SELECT salary_increment_pkey
                                                                            FROM salary_increment
                                                                            ORDER BY salary_increment_pkey DESC
                                                                            LIMIT 1;
                                                                            ");
            $salary_increment_pkey = isset($arr_salary_increment_pkey[0]['salary_increment']['salary_increment_pkey']) ? $arr_salary_increment_pkey[0]['salary_increment']['salary_increment_pkey'] : 0;

            $arr_data = [];

            foreach ($arr_form_data as $key => $value) {
                if (preg_match('/^new_value_(\d+)$/', $key, $matches)) {
                    $number = $matches[1];

                    $arr_data[] = [
                        'salary_increment_fkey' => $salary_increment_pkey,
                        'emp_fkey' => $emp_fkey,
                        'salary_head_item_fkey' => $number,
                        'salary_head_item_desc' => isset($arr_form_data["desc_$number"]) ? trim($arr_form_data["desc_$number"]) : '',
                        'current_value' => isset($arr_form_data["current_$number"]) ? trim($arr_form_data["current_$number"]) : '',
                        'new_value' => $value,
                        'increment_amt' => isset($arr_form_data["new_value_amt$number"]) ? $arr_form_data["new_value_amt$number"] : '',
                        'increment_perc' => isset($arr_form_data["new_value_pct$number"]) ? $arr_form_data["new_value_pct$number"] : '',
                    ];
                }

                // Handle indirect salary heads
                if (preg_match('/^indirect_new_value_(\d+)$/', $key, $matches)) {
                    $number = $matches[1];

                    $arr_data[] = [
                        'salary_increment_fkey' => $salary_increment_pkey,
                        'emp_fkey' => $emp_fkey,
                        'salary_head_item_fkey' => $number,
                        'salary_head_item_desc' => isset($arr_form_data["desc_$number"]) ? trim($arr_form_data["desc_$number"]) : '',
                        'current_value' => isset($arr_form_data["indirect_current_$number"]) ? trim($arr_form_data["indirect_current_$number"]) : '',
                        'new_value' => $value,
                        'increment_amt' => isset($arr_form_data["indirect_new_value_amt$number"]) ? $arr_form_data["indirect_new_value_amt$number"] : '',
                        'increment_perc' => isset($arr_form_data["indirect_new_value_pct$number"]) ? $arr_form_data["indirect_new_value_pct$number"] : '',
                    ];
                }
            }


            $arr_structure = $this->EmployeeCTC->query("SELECT * FROM emp_salary_structure
                                                        WHERE emp_fkey = '$emp_fkey'
                                                        AND head_operator = 'Addition'
                                                        AND end_date_effective IS NULL;");

            foreach ($arr_structure as &$structure) {
                $item_fkey = $structure['emp_salary_structure']['salary_head_item_fkey'];
                $emp_salary_slip_pkey = $structure['emp_salary_structure']['emp_salary_structure_pkey'];

                foreach ($arr_data as $data) {
                    if ($data['salary_head_item_fkey'] == $item_fkey) {
                        $structure['emp_salary_structure']['structure_det_value'] = $data['new_value'];

                        $arr_emp_salary_slip_data = array(
                            'EmployeeSalaryStructure.structure_det_value' => $data['new_value'] //edited by sinsiya on 26-09-2024
                        );
                        $this->EmployeeSalaryStructure->updateAll(
                            $arr_emp_salary_slip_data,
                            array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                        );
                        break;
                    }
                }
            }

            if ($salary_increment_pkey != 0) {
                $sql = "INSERT INTO salary_increment_details (
                    salary_increment_fkey,
                    emp_fkey,
                    salary_head_item_fkey,
                    salary_head_item_desc,
                    current_value,
                    new_value,
                    increment_amt,
                    increment_perc,
                    status
                  ) VALUES ";

                $values = [];
                foreach ($arr_data as $data) {
                    // Escape and format values
                    $salary_increment_fkey = (int) $data['salary_increment_fkey'];
                    $emp_fkey = (int) $data['emp_fkey'];
                    $salary_head_item_fkey = (int) $data['salary_head_item_fkey'];
                    $salary_head_item_desc = addslashes($data['salary_head_item_desc']);
                    $current_value = (float) $data['current_value'];
                    $new_value = (float) $data['new_value'];
                    $increment_amt = (float) $data['increment_amt'];
                    $increment_perc = (float) $data['increment_perc'];
                    $status = 1;

                    $values[] = "(
                        $salary_increment_fkey,
                        $emp_fkey,
                        $salary_head_item_fkey,
                        '$salary_head_item_desc',
                        $current_value,
                        $new_value,
                        $increment_amt,
                        $increment_perc,
                        $status
                      )";
                }

                $sql .= implode(",\n", $values);

                // Now run the query using CakePHP or raw DB connection
                $this->SalaryIncrementDetails->query($sql); // If using CakePHP 2.x

                // $arr_form_data['salary_structure']

            }

            $result = $this->EmployeeCTC->query("select arrear_month_fn('$branch','$month','$user_id')"); // Arrear

            $this->SalaryIncrementDetails->query(
                "UPDATE emp_proff SET structure_id = ? WHERE emp_fkey = ?",
                [$salary_structure, $emp_fkey]
            );


            echo json_encode([
                'status' => 'success',
                'message' => 'Salary increment saved successfully.',
                'increment_id' => $salary_increment_pkey
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to save increment.'
            ]);
        }



        // unset($structure); // Break reference
        // exit;
    }
    // End

    // Edited by Akshay on 17-4-2025
    public function itemIncrementForm()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->render('item_increment');
    }

    public function saveItemIncrement()
    {
        try {
            $this->autoRender = FALSE;
            $this->response->type('json');
            $this->ComponentIncrement->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;

            $user_id = $this->Session->read("login_user_id");
            $arr_form_data['created_by'] = $user_id;
            $arr_form_data['created_date'] = date('Y-m-d');

            $this->ComponentIncrement->save($arr_form_data);

            $latest = $this->ComponentIncrement->find('first', [
                'conditions' => ['ComponentIncrement.status' => 1],
                'order' => ['ComponentIncrement.sal_pkey DESC'],
                'fields' => ['ComponentIncrement.sal_pkey'],
                'recursive' => -1
            ]);

            $latestSalPkey = $latest['ComponentIncrement']['sal_pkey'];

            echo json_encode([
                'success' => true, // Used to check status in JS
                'message' => 'Salary increment saved successfully.', // Custom message
                'increment_id' => $latestSalPkey // Return the latest insert ID or anything else needed
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to save increment.'
            ]);
        }
    }
    // End

    public function onboarding($emp_pkey = null)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        $company_code = $this->Session->read('company_code');
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
        $this->Education->useDbConfig = $this->Session->read('ds');
        $this->WorkExperience->useDbConfig = $this->Session->read('ds');
        // debug($company_code);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        $this->set('plan', $plan);
        $this->set('company_code', $company_code);
        //end

        $company_code = strtoupper($this->Session->read('company_code'));
        // debug($company_code);
        // exit;
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        //  $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->findByEmpFkey($emp_pkey);

        //     // debug($arr_emp_professional_profile);
        //     $this->set('arr_professionalinfo', $arr_emp_professional_profile['EmployeeProfessionalDetails']);
        $this->set('user_group', $user_group);

        // NOTE: incoming $emp_pkey is actually emp_join_pkey from URL
        if ($emp_pkey) {
            // store the emp_join_pkey for views if needed
            // debug( $emp_pkey);
            $this->set('emp_pkey', $emp_pkey);

            $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

            // --- Fetch emp_fkey from emp_join using emp_join_pkey (incoming $emp_pkey) ---
            $empJoin = $this->EmployeeJoin->find('first', [
                'conditions' => ['EmployeeJoin.emp_join_pkey' => $emp_pkey],
                'fields' => ['emp_fkey']
            ]);
            $emp_fkey = 0;
            if (!empty($empJoin) && isset($empJoin['EmployeeJoin']['emp_fkey'])) {
                $emp_fkey = (int)$empJoin['EmployeeJoin']['emp_fkey'];
            }

            // Use emp_fkey for employee-specific queries.
            // Always call loadEmpDetails (even if 0) as you requested.
            $this->loadEmpDetails($emp_fkey);

            // If emp_fkey is valid (non-zero) load profile details, else set default empty.
            if (!empty($emp_fkey) && $emp_fkey != 0) {
                $empDetails = $this->loadEmpProfDetails($emp_fkey);
                $this->set('empDetails', $empDetails);
            } else {
                $this->set('empDetails', []);
            }
            // debug($empJoin);

            // Use emp_fkey when querying emp_ctc_transaction (it uses emp_fkey)
            $arr_gross = [];
            if ($emp_fkey != 0) {
                $arr_gross = $this->EmployeeDetails->query(
                    "select emp_anual_ctc from emp_ctc_transaction where emp_fkey = {$emp_fkey} and end_date_effective is null "
                );
            }
            $this->set('arr_gross', $arr_gross);

            // If user_group == 2 (employee), fetch payro_priv using emp_fkey
            if ($user_group == 2) {
                $payroUser = $this->EmployeeProfessionalDetails->query(
                    "select payro_priv from emp_proff where emp_fkey ='{$emp_fkey}'"
                );
                $this->set('payroUser', $payroUser);
            }

            // --- Rest of the function expects emp_fkey for employee-specific data ---
            // For checks that rely on emp_details row:
            $arr_emp_pkey = [];
            if ($emp_fkey != 0) {
                $arr_emp_pkey = $this->EmployeeDetails->query(
                    "SELECT * FROM `emp_details` WHERE `emp_pkey` = '{$emp_fkey}'"
                );
                // normalize values
                $emp_pkey_from_details = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
                $branch_code = isset($arr_emp_pkey[0]['emp_details']['branch_code']) ? $arr_emp_pkey[0]['emp_details']['branch_code'] : '';
            } else {
                $emp_pkey_from_details = 0;
                $branch_code = '';
            }
            // debug($branch_code);
            $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
            $yearmonth = $monthdd;

            // pass emp_fkey (from details) to time_duration_check
            // $queryResult = $this->EmployeeDetails->query("SELECT  time_duration_check('{$yearmonth}', '{$emp_pkey_from_details}', '{$branch_code}')");
            // if (!($queryResult)) {
            //     return false;
            // }

            // EditPunches checks
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            // $branch = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='{$emp_pkey_from_details}' ");
            $branch_code = 'NULL';
            $month = date('Y-m-01');
            $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='{$emp_pkey_from_details}' and yearmonth='{$month}' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('{$month}','%Y-%m') ) ");

            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }
            if ($this->Session->read('company_code') == 'DEMO' || $this->Session->read('company_code') == 'KWMT') {
                $arr_emp_professional_profile['emp_category'] = '';
            }
            $this->set('head', 'New Employee');
            // $this->set('emp_pkey', 0);
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            $this->set('arr_taxationinfo', array());
        }
        // debug($arr_emp_personal_profile);
        // exit;
        // --- the rest of the method (departments, branches, years, etc) ---
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);
        // debug($arr_departments);

        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);
        // debug($arr_designations);

        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        if ($company_code == 'DEMO' || $company_code == 'KWMT') {
            $arr_category = $this->EmployeeDetails->query("SELECT category_pkey,category_code,category_name FROM category WHERE status = 1 AND category_pkey in (SELECT category_fkey FROM grade WHERE status = 1 AND category_fkey != 0) ORDER BY category_name ASC;");
            $this->set('arr_category', $arr_category);
        }

        // $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        // $this->set('arr_verticals', $arr_verticals);

        $cur_emp_key = $this->Session->read("emp_fkey");
        $user = strtoupper($this->Session->read('company_code'));

        if ($company_code == 'VGFS' || $company_code == 'DEMO' || $company_code == 'GLET' || $company_code == 'VSFS') {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        }
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        // debug($arr_branches);
        // exit;
        if (count($arr_branches) == 0) {
            $user_group = $this->Session->read('user_group');
            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );

                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }

        $this->set('arr_branches', $arr_branches);
        $this->set('user', $user);
        // debug($arr_branches);
        // exit;

        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        // $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        // $this->set('arr_salary', $arr_salary);

        // $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        // $years = $this->FinancialYear->find("all");
        // $this->set('years', $years);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $hierarchy_set = $this->NoticePeriod->query("select * from emp_details where emp_pkey in (select attr1 from emp_proff where emp_fkey = '$emp_pkey') ");
        $this->set('hierarchy_set', $hierarchy_set);
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_pkey` = '$emp_pkey'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        $yearmonth = $monthdd;
        // $queryResult = $this->EmployeeDetails->query("SELECT  time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')");
        // if (!($queryResult)) {
        //     return false;
        // }
        //edited by athira on 29-04-2025
        // $this->EditPunches->useDbConfig = $this->Session->read('ds');
        // $branch = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='$emp_pkey' ");
        // $branch_code = 'NULL';
        // $month = date('Y-m-01');
        // $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        // if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
        //     return FALSE;
        //     die();
        // }
        // if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
        //     if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // } else {
        //     if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // }
    }

    public function allonboard($emp_pkey = null)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        $company_code = $this->Session->read('company_code');
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
        $this->Education->useDbConfig = $this->Session->read('ds');
        $this->WorkExperience->useDbConfig = $this->Session->read('ds');

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        $this->set('plan', $plan);
        $this->set('company_code', $company_code);
        //end

        $company_code = strtoupper($this->Session->read('company_code'));
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->findByEmpFkey($emp_pkey);

        // debug($arr_emp_professional_profile);
        $this->set('arr_professionalinfo', $arr_emp_professional_profile['EmployeeProfessionalDetails']);
        $this->set('user_group', $user_group);

        // NOTE: incoming $emp_pkey is actually emp_join_pkey from URL
        if ($emp_pkey) {
            // store the emp_join_pkey for views if needed
            $this->set('emp_pkey', $emp_pkey);
            $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

            // --- Fetch emp_fkey from emp_join using emp_join_pkey (incoming $emp_pkey) ---
            $empJoin = $this->EmployeeJoin->find('first', [
                'conditions' => ['EmployeeJoin.emp_join_pkey' => $emp_pkey],
                'fields' => ['emp_fkey']
            ]);
            $emp_fkey = 0;
            if (!empty($empJoin) && isset($empJoin['EmployeeJoin']['emp_fkey'])) {
                $emp_fkey = (int)$empJoin['EmployeeJoin']['emp_fkey'];
            }

            // Use emp_fkey for employee-specific queries.
            // Always call loadEmpDetails (even if 0) as you requested.
            $this->loadEmpDetails($emp_fkey);

            // If emp_fkey is valid (non-zero) load profile details, else set default empty.
            if (!empty($emp_fkey) && $emp_fkey != 0) {
                $empDetails = $this->loadEmpProfDetails($emp_fkey);
                $this->set('empDetails', $empDetails);
            } else {
                $this->set('empDetails', []);
            }

            // Use emp_fkey when querying emp_ctc_transaction (it uses emp_fkey)
            $arr_gross = [];
            if ($emp_fkey != 0) {
                $arr_gross = $this->EmployeeDetails->query(
                    "select emp_anual_ctc from emp_ctc_transaction where emp_fkey = {$emp_fkey} and end_date_effective is null "
                );
            }
            $this->set('arr_gross', $arr_gross);

            // If user_group == 2 (employee), fetch payro_priv using emp_fkey
            if ($user_group == 2) {
                $payroUser = $this->EmployeeProfessionalDetails->query(
                    "select payro_priv from emp_proff where emp_fkey ='{$emp_fkey}'"
                );
                $this->set('payroUser', $payroUser);
            }

            // --- Rest of the function expects emp_fkey for employee-specific data ---
            // For checks that rely on emp_details row:
            $arr_emp_pkey = [];
            if ($emp_fkey != 0) {
                $arr_emp_pkey = $this->EmployeeDetails->query(
                    "SELECT * FROM `emp_details` WHERE `emp_pkey` = '{$emp_fkey}'"
                );
                // normalize values
                $emp_pkey_from_details = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
                $branch_code = isset($arr_emp_pkey[0]['emp_details']['branch_code']) ? $arr_emp_pkey[0]['emp_details']['branch_code'] : '';
            } else {
                $emp_pkey_from_details = 0;
                $branch_code = '';
            }

            $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
            $yearmonth = $monthdd;

            // pass emp_fkey (from details) to time_duration_check
            // $queryResult = $this->EmployeeDetails->query("SELECT  time_duration_check('{$yearmonth}', '{$emp_pkey_from_details}', '{$branch_code}')");
            //     if (!($queryResult)) {
            //         return false;
            //     }

            //     // EditPunches checks
            //     $this->EditPunches->useDbConfig = $this->Session->read('ds');
            //     $branch = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='{$emp_pkey_from_details}' ");
            //     $branch_code = 'NULL';
            //     $month = date('Y-m-01');
            //     $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='{$emp_pkey_from_details}' and yearmonth='{$month}' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('{$month}','%Y-%m') ) ");

            //     if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '{$emp_pkey_from_details}' )")) {
            //         return FALSE;
            //     }

            //     if ($shiftdetailed[0]['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            //         if (!$this->EditPunches->query("SELECT time_duration_check_multishift('{$month}', '{$emp_pkey_from_details}', '{$branch_code}')")) {
            //             return false;
            //         }
            //     } else {
            //         if (!$this->EditPunches->query("SELECT time_duration_check('{$month}', '{$emp_pkey_from_details}', '{$branch_code}')")) {
            //             return false;
            //         }
            //     }
            // } else {
            // New employee flow (no emp_join_pkey passed)
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }
            if ($this->Session->read('company_code') == 'DEMO' || $this->Session->read('company_code') == 'KWMT') {
                $arr_emp_professional_profile['emp_category'] = '';
            }
            $contractData = $this->EmployeeDetails->query(
                "SELECT * FROM contracted_days WHERE emp_fkey = '{$emp_pkey}' ORDER BY contracted_days_pkey DESC LIMIT 1"
            );
            // debug($arr_emp_professional_profile);
            // exit;

            $this->set('head', 'New Employee');
            // $this->set('emp_pkey', 0);
            // $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $arr_emp_personal_profile['emp_branch'] = $branch_code; // <-- add this line
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            $this->set('arr_taxationinfo', array());
            $this->set('arr_contract', $contractData);
        }
        // debug($arr_emp_personal_profile);
        // debug($arr_emp_professional_profile);

        // --- the rest of the method (departments, branches, years, etc) ---
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);
        // debug($arr_departments);
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        if ($company_code == 'DEMO' || $company_code == 'GLET' || $company_code == 'KWMT') {
            $arr_category = $this->EmployeeDetails->query("SELECT category_pkey,category_code,category_name FROM category WHERE status = 1 AND category_pkey in (SELECT category_fkey FROM grade WHERE status = 1 AND category_fkey != 0) ORDER BY category_name ASC;");
            $this->set('arr_category', $arr_category);
        }
        $empGradeId = isset($arr_emp_professional_profile['EmployeeProfessionalDetails']['emp_grade'])
            ? $arr_emp_professional_profile['EmployeeProfessionalDetails']['emp_grade']
            : null;


        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        $cur_emp_key = $this->Session->read("emp_fkey");
        $user = strtoupper($this->Session->read('company_code'));

        if ($company_code == 'VGFS' || $company_code == 'DEMO' || $company_code == 'GLET' || $company_code == 'VSFS') {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        }
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        if (count($arr_branches) == 0) {
            $user_group = $this->Session->read('user_group');
            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );

                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }


        $arr_end_date = $this->EmployeeDetails->query("SELECT * FROM `user_credentials` WHERE `emp_fkey` = '$emp_pkey'");
        $this->set('arr_end_date', $arr_end_date);

        $this->set('arr_branches', $arr_branches);
        $this->set('user', $user);

        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        // $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        // $this->set('arr_salary', $arr_salary);

        // $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        // $years = $this->FinancialYear->find("all");
        // $this->set('years', $years);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);
// --- Fetch supervisor info for display ---
$superiorEmpKey = !empty($arr_emp_professional_profile['EmployeeProfessionalDetails']['attr1']) 
                  ? $arr_emp_professional_profile['EmployeeProfessionalDetails']['attr1'] 
                  : null;

$superiorText = '';

if (!empty($superiorEmpKey)) {
    // Get supervisor emp_company_id from emp_proff
    $supervisorProff = $this->EmployeeProfessionalDetails->query(
        "SELECT emp_proff.emp_company_id, emp_details.first_name, emp_details.last_name
         FROM emp_proff 
         LEFT JOIN emp_details ON emp_details.emp_pkey = emp_proff.emp_fkey
         WHERE emp_proff.emp_fkey = '$superiorEmpKey' 
         LIMIT 1"
    );

    if (!empty($supervisorProff[0])) {
        $firstName = isset($supervisorProff[0]['emp_details']['first_name']) 
                        ? $supervisorProff[0]['emp_details']['first_name'] 
                        : '';
        $lastName  = isset($supervisorProff[0]['emp_details']['last_name']) 
                        ? $supervisorProff[0]['emp_details']['last_name'] 
                        : '';
        $companyId = isset($supervisorProff[0]['emp_proff']['emp_company_id']) 
                        ? $supervisorProff[0]['emp_proff']['emp_company_id'] 
                        : '';

        $superiorText = trim($firstName . ' ' . $lastName);
        if (!empty($companyId)) {
            $superiorText .= ' - ' . $companyId;
        }
    }
}

// Pass to view
$this->set(compact('superiorText', 'superiorEmpKey'));
        $hierarchy_set = $this->NoticePeriod->query("select * from emp_details where emp_pkey in (select attr1 from emp_proff where emp_fkey = '$emp_pkey') ");
        $this->set('hierarchy_set', $hierarchy_set);
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_pkey` = '$emp_pkey'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        $yearmonth = $monthdd;
        // $queryResult = $this->EmployeeDetails->query("SELECT  time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')");
        // if (!($queryResult)) {
        //     return false;
        // }
        //edited by athira on 29-04-2025
        // $this->EditPunches->useDbConfig = $this->Session->read('ds');
        // $branch = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='$emp_pkey' ");
        // $branch_code = 'NULL';
        // $month = date('Y-m-01');
        // $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        // if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
        //     return FALSE;
        //     die();
        // }
        // if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
        //     if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // } else {
        //     if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // }
    }
    // public function updateOnboardData()
    // {
    //     $this->autoRender = false;

    //     $ds = $this->Session->read('ds');
    //     $this->EmployeeProfessionalDetails->useDbConfig = $ds;
    //     $this->EmployeeConfig->useDbConfig = $ds;
    //     $this->UserCredentials->useDbConfig = $ds;

    //     if ($this->request->is('post')) {
    //         $data = $this->request->data;
    //         // debug($data);

    //         $emp_fkey = !empty($data['emp_pkey']) ? (int)$data['emp_pkey'] : 0;
    //         $user = strtoupper($this->Session->read('company_code'));
    //         $user_id = $this->Session->read("login_user_id");

    //         $updateData = [
    //             'EmployeeProfessionalDetails.joining_date'  => !empty($data['joining_date']) ? "'" . $data['joining_date'] . "'" : 'NULL',
    //             'EmployeeProfessionalDetails.emp_type'       => !empty($data['emp_type']) ? "'" . $data['emp_type'] . "'" : 'NULL',
    //             'EmployeeProfessionalDetails.designation'    => !empty($data['designation']) ? "'" . $data['designation'] . "'" : 'NULL',
    //             'EmployeeProfessionalDetails.emp_dept'       => !empty($data['emp_dept']) ? "'" . $data['emp_dept'] . "'" : 'NULL',
    //             'EmployeeProfessionalDetails.emp_grade'      => !empty($data['emp_grade']) ? (int)$data['emp_grade'] : 'NULL',
    //             'EmployeeProfessionalDetails.emp_branch'     => !empty($data['emp_branch']) ? "'" . $data['emp_branch'] . "'" : 'NULL',
    //             'EmployeeProfessionalDetails.notice_days'    => !empty($data['notice_days']) ? (int)$data['notice_days'] : 'NULL',
    //             'EmployeeProfessionalDetails.probation'      => !empty($data['probation']) ? "'" . $data['probation'] . "'" : 'NULL'
    //         ];


    //         // debug($updateData);

    //         $this->EmployeeProfessionalDetails->updateAll(
    //             $updateData,
    //             ['EmployeeProfessionalDetails.emp_fkey' => $emp_fkey]
    //         );

    //         // 🧩 Update Credentials
    //         $updateCred = [
    //             'UserCredentials.end_date'   => !empty($data['end_date']) ? $data['end_date'] : null,
    //             'UserCredentials.user_id'    => !empty($data['emp_company_id']) ? $data['emp_company_id'] : null
    //         ];

    //         $this->UserCredentials->updateAll(
    //             $updateCred,
    //             ['UserCredentials.emp_fkey' => $emp_fkey]
    //         );

    //         // 🧩 Update Configs (GRADE, NOTICEPER)
    //         $this->EmployeeConfig->updateAll(
    //             ['EmployeeConfig.status' => 0],
    //             [
    //                 'EmployeeConfig.emp_fkey' => $emp_fkey,
    //                 'type IN' => ['GRADE', 'NOTICEPER'],
    //                 'EmployeeConfig.status' => 1
    //             ]
    //         );

    //         // 🧩 Insert New Configs
    //         $arr_emp_config = [
    //             [
    //                 'type'          => 'GRADE',
    //                 'company_code'  => $user,
    //                 'emp_fkey'      => $emp_fkey,
    //                 'month_year'    => !empty($data['month_year']) ? $data['month_year'] : null,
    //                 'policy_id'     => !empty($data['emp_grade']) ? (int)$data['emp_grade'] : null,
    //                 'creation_date' => date("Y-m-d H:i:s"),
    //                 'created_by'    => $user_id,
    //                 'status'        => 1
    //             ],
    //             [
    //                 'type'          => 'NOTICEPER',
    //                 'company_code'  => $user,
    //                 'emp_fkey'      => $emp_fkey,
    //                 'month_year'    => !empty($data['month_year']) ? $data['month_year'] : null,
    //                 'policy_id'     => !empty($data['notice_days']) ? (int)$data['notice_days'] : null,
    //                 'creation_date' => date("Y-m-d H:i:s"),
    //                 'created_by'    => $user_id,
    //                 'status'        => 1
    //             ]
    //         ];

    //         $this->EmployeeConfig->create();
    //         $this->EmployeeConfig->saveMany($arr_emp_config);

    //         echo json_encode(['success' => true, 'message' => 'Onboarding data updated successfully.']);
    //         return;
    //     }

    //     echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    // }
    public function updateOnboardData()
    {
        $this->autoRender = false;

        $ds = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $ds;
        $this->EmployeeConfig->useDbConfig = $ds;
        $this->UserCredentials->useDbConfig = $ds;

        if ($this->request->is('post')) {

            $data = $this->request->data;
            $emp_fkey = !empty($data['emp_pkey']) ? (int)$data['emp_pkey'] : 0;

            if ($emp_fkey <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid employee key.']);
                return;
            }

            // ================================
            // 1. FETCH EXISTING DATA
            // ================================
            $existingProf = $this->EmployeeProfessionalDetails->find('first', [
                'conditions' => ['emp_fkey' => $emp_fkey],
                'recursive'  => -1
            ]);

            $existingCred = $this->UserCredentials->find('first', [
                'conditions' => ['emp_fkey' => $emp_fkey],
                'recursive'  => -1
            ]);

            if (empty($existingProf)) {
                echo json_encode(['success' => false, 'message' => 'Employee details not found.']);
                return;
            }

            // ================================
            // 2. PROFESSIONAL DETAILS UPDATE
            // ================================
            $updateProf = [];

            $fields = [
                'joining_date',
                'emp_type',
                'designation',
                'emp_dept',
                'emp_grade',
                'emp_branch',
                'notice_days',
                'probation'
            ];

            foreach ($fields as $col) {

                $newValue = isset($data[$col]) ? trim($data[$col]) : null;
                $oldValue = $existingProf['EmployeeProfessionalDetails'][$col];

                // Normalize empty to NULL
                if ($newValue === "" || $newValue === null) {
                    $newValueClean = null;
                } else {
                    $newValueClean = $newValue;
                }

                if ($newValueClean != $oldValue) {
                    $updateProf["EmployeeProfessionalDetails.$col"] =
                        ($newValueClean === null) ? "NULL" : "'" . $newValueClean . "'";
                }
            }

            if (!empty($updateProf)) {
                $this->EmployeeProfessionalDetails->updateAll(
                    $updateProf,
                    ['EmployeeProfessionalDetails.emp_fkey' => $emp_fkey]
                );
            }

            // ================================
            // 3. USER CREDENTIAL UPDATE
            // ================================
            // if (!empty($existingCred)) {

            //     $updateCred = [];

            //     // END DATE
            //     $newEndDate = !empty($data['end_date']) ? $data['end_date'] : null;
            //     $oldEndDate = $existingCred['UserCredentials']['end_date'];

            //     if ($newEndDate != $oldEndDate) {
            //         $updateCred['UserCredentials.end_date'] =
            //             $newEndDate ? "'" . $newEndDate . "'" : "NULL";
            //     }

            //     // USER ID
            //     $newUserId = !empty($data['emp_company_id']) ? $data['emp_company_id'] : null;
            //     $oldUserId = $existingCred['UserCredentials']['user_id'];

            //     if ($newUserId != $oldUserId) {
            //         $updateCred['UserCredentials.user_id'] =
            //             $newUserId ? "'" . $newUserId . "'" : "NULL";
            //     }

            //     if (!empty($updateCred)) {
            //         $this->UserCredentials->updateAll(
            //             $updateCred,
            //             ['UserCredentials.emp_fkey' => $emp_fkey]
            //         );
            //     }
            // }

            // 1. Fetch contract record by emp_fkey
            $contractData = $this->EmployeeProfessionalDetails->query("
    SELECT contracted_days_pkey 
    FROM contracted_days 
    WHERE emp_fkey = '{$emp_fkey}' 
    AND status = 1 
    ORDER BY contracted_days_pkey DESC 
    LIMIT 1
");

            // 2. New End Date from form
            $newEndDate = !empty($data['end_date']) ? $data['end_date'] : null;

            // 3. Update end_date only if contract row exists
            if (!empty($contractData)) {

                $cd_pkey = $contractData[0]['contracted_days']['contracted_days_pkey'];

                $this->EmployeeProfessionalDetails->query("
        UPDATE contracted_days
        SET contract_end_date = '{$newEndDate}',
            modified_time = NOW()
        WHERE contracted_days_pkey = '{$cd_pkey}'
    ");
            }

            // ================================
            // 4. CONFIG UPDATES (GRADE + NOTICE)
            // ================================
            $gradeChanged  =
                $existingProf['EmployeeProfessionalDetails']['emp_grade'] != $data['emp_grade'];

            $noticeChanged =
                $existingProf['EmployeeProfessionalDetails']['notice_days'] != $data['notice_days'];

            if ($gradeChanged || $noticeChanged) {

                // Disable previous configs
                $this->EmployeeConfig->updateAll(
                    ['EmployeeConfig.status' => 0],
                    [
                        'EmployeeConfig.emp_fkey' => $emp_fkey,
                        'type IN' => ['GRADE', 'NOTICEPER'],
                        'EmployeeConfig.status' => 1
                    ]
                );

                // Insert new active configs
                $this->EmployeeConfig->saveMany([
                    [
                        'type'          => 'GRADE',
                        'company_code'  => $this->Session->read('company_code'),
                        'emp_fkey'      => $emp_fkey,
                        // 'month_year'    => $data['month_year'],
                        'policy_id'     => $data['emp_grade'],
                        'creation_date' => date("Y-m-d H:i:s"),
                        'created_by'    => $this->Session->read("login_user_id"),
                        'status'        => 1
                    ],
                    [
                        'type'          => 'NOTICEPER',
                        'company_code'  => $this->Session->read('company_code'),
                        'emp_fkey'      => $emp_fkey,
                        // 'month_year'    => $data['month_year'],
                        'policy_id'     => $data['notice_days'],
                        'creation_date' => date("Y-m-d H:i:s"),
                        'created_by'    => $this->Session->read("login_user_id"),
                        'status'        => 1
                    ]
                ]);
            }

            echo json_encode(['success' => true, 'message' => 'Onboarding data updated successfully.']);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }


    public function saveAllOnboard()
    {
        $this->autoRender = false;

        // Set DB configs
        $ds = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $ds;
        $this->EmployeeProfessionalDetails->useDbConfig = $ds;
        $this->EmployeeConfig->useDbConfig = $ds;
        $this->UserCredentials->useDbConfig = $ds;

        $this->EmployeeJoin->useDbConfig = $ds;
        $user_id = $this->Session->read("login_user_id");
        $created_by = $user_id;

        if ($this->request->is('post')) {
            $arr_query_data = $this->request->data;
            // debug($arr_query_data);
            $emp_fkey = isset($arr_query_data['emp_fkey']) ? $arr_query_data['emp_fkey'] : 0;
            // debug($emp_fkey);
            // // --- Update EmployeeDetails ---
            // if (!empty($data['EmployeeDetails']) && $emp_fkey != 0) {
            //     $this->EmployeeDetails->id = $emp_fkey;
            //     $this->EmployeeDetails->save($data['EmployeeDetails']);
            // }
            $user = strtoupper($this->Session->read('company_code'));

            $existingData = $this->EmployeeProfessionalDetails->find('first', [
                'conditions' => ['emp_fkey' => $emp_fkey],
                'recursive' => -1
            ]);
            // debug($existingData);
            $existingCred = $this->UserCredentials->find('first', [
                'conditions' => ['emp_fkey' => $emp_fkey],
                'recursive' => -1
            ]);
          $emp_company_id = isset($arr_query_data['emp_company_id']) ? $arr_query_data['emp_company_id'] : '';
if (!empty($emp_company_id)) {

    $existingCompanyId = $this->EmployeeProfessionalDetails->find('first', [
        'conditions' => [
            'EmployeeProfessionalDetails.emp_company_id' => $emp_company_id,
            'EmployeeProfessionalDetails.emp_fkey !=' => $emp_fkey
        ],
        'recursive' => -1
    ]);

    if (!empty($existingCompanyId)) {

        $existing_emp_fkey = $existingCompanyId['EmployeeProfessionalDetails']['emp_fkey'];

        $empNameData = $this->EmployeeDetails->find('first', [
            'conditions' => ['EmployeeDetails.emp_pkey' => $existing_emp_fkey],
            'fields' => ['emp_name'],
            'recursive' => -1
        ]);

        $emp_name = !empty($empNameData) ? $empNameData['EmployeeDetails']['emp_name'] : 'Unknown';

        echo json_encode([
            'success' => false,
            'message' => "Employee Company ID already exists. Employee Name: ".$emp_name
        ]);
        return;
    }
   $companycode = $this->Session->read('company_code');


}
            // debug($existingCred);
            $this->EmployeeProfessionalDetails->useDbConfig = $ds;


            // $updateData = [
            //     'emp_fkey' =>$emp_fkey,
            //     'joining_date'       => isset($arr_query_data['joining_date']) ? $arr_query_data['joining_date'] : null,
            //     // 'emp_company_id'     => isset($arr_query_data['company_code']) ? $arr_query_data['company_code'] : null,
            //     'emp_type'           => isset($arr_query_data['emp_type']) ? $arr_query_data['emp_type'] : null,
            //     'designation'        => isset($arr_query_data['designation']) ? $arr_query_data['designation'] : null,
            //     // 'section'            => isset($arr_query_data['section']) ? $arr_query_data['section'] : null,
            //     // 'division'           => isset($arr_query_data['division']) ? $arr_query_data['division'] : null,
            //     'emp_dept'           => isset($arr_query_data['emp_dept']) ? $arr_query_data['emp_dept'] : null,
            //     'emp_grade'          => isset($arr_query_data['emp_grade']) ? $arr_query_data['emp_grade'] : null,
            //     // 'emp_vertical'       => isset($arr_query_data['division']) ? $arr_query_data['division'] : null,
            //     'emp_branch'         => isset($arr_query_data['emp_branch']) ? $arr_query_data['emp_branch'] : null,
            //     'notice_days'        => isset($arr_query_data['notice_days']) ? $arr_query_data['notice_days'] : null,
            //     'probation'          => isset($arr_query_data['probation']) ? $arr_query_data['probation'] : null,
            //     // 'HOLIDAY_GROUP_ID'   => isset($arr_query_data['HOLIDAY_GROUP_ID']) ? $arr_query_data['HOLIDAY_GROUP_ID'] : null,
            //     // 'LEAVEPOLICY_GROUP_ID' =>isset($arr_query_data['LEAVEPOLICY_GROUP_ID']) ? $arr_query_data['LEAVEPOLICY_GROUP_ID'] : null,
            //     // 'emp_sep_priv'       => !empty($arr_query_data['section']) ? "'" . $arr_query_data['section'] . "'" : "NULL",
            //     // 'emp_mgt_priv'       => !empty($arr_query_data['previous_employer_gratuity']) ? $arr_query_data['previous_employer_gratuity'] : 0,
            // ];

            $updateData = [
                'EmployeeProfessionalDetails.joining_date' => !empty($arr_query_data['joining_date'])
                    ? "'" . $arr_query_data['joining_date'] . "'"
                    : "'" . $existingData['EmployeeProfessionalDetails']['joining_date'] . "'",
                'EmployeeProfessionalDetails.emp_company_id' => !empty($arr_query_data['emp_company_id'])
                    ? "'" . $arr_query_data['emp_company_id'] . "'"
                    : "'" . $existingData['EmployeeProfessionalDetails']['emp_company_id'] . "'",

                'EmployeeProfessionalDetails.emp_type' => !empty($arr_query_data['emp_type'])
                    ? "'" . $arr_query_data['emp_type'] . "'"
                    : "'" . $existingData['EmployeeProfessionalDetails']['emp_type'] . "'",

                'EmployeeProfessionalDetails.designation' => !empty($arr_query_data['designation'])
                    ? "'" . $arr_query_data['designation'] . "'"
                    : "'" . $existingData['EmployeeProfessionalDetails']['designation'] . "'",

                'EmployeeProfessionalDetails.emp_dept' => !empty($arr_query_data['emp_dept'])
                    ? "'" . $arr_query_data['emp_dept'] . "'"
                    : "'" . $existingData['EmployeeProfessionalDetails']['emp_dept'] . "'",

                'EmployeeProfessionalDetails.emp_grade' =>
        !empty($arr_query_data['emp_grade'])
            ? (int)$arr_query_data['emp_grade']
            : 'NULL',

                'EmployeeProfessionalDetails.emp_branch' => !empty($arr_query_data['emp_branch'])
                    ? "'" . $arr_query_data['emp_branch'] . "'"
                    : "'" . $existingData['EmployeeProfessionalDetails']['emp_branch'] . "'",

                'EmployeeProfessionalDetails.notice_days' => !empty($arr_query_data['notice_days'])
                    ? $arr_query_data['notice_days']
                    : $existingData['EmployeeProfessionalDetails']['notice_days'],

                'EmployeeProfessionalDetails.probation' => !empty($arr_query_data['probation'])
                    ? $arr_query_data['probation']
                    : $existingData['EmployeeProfessionalDetails']['probation']
            ];


            // Optional attrs
            for ($i = 1; $i <= 6; $i++) {
                $attr = 'attr' . $i;
                if (isset($arr_query_data[$attr])) {
                    $updateData[$attr] = "'" . $arr_query_data[$attr] . "'";
                }
            }
            // debug($updateData);

            // Run updateAll
            $this->EmployeeProfessionalDetails->updateAll(
                $updateData,
                ['emp_fkey' => $emp_fkey]
            );


            // $updateCred = [
            //     'UserCredentials.start_date' => !empty($arr_query_data['start_date'])
            //         ? "'" . $arr_query_data['start_date'] . "'"
            //         : "'" . $existingCred['UserCredentials']['start_date'] . "'",

            //     // 'UserCredentials.end_date' => !empty($arr_query_data['end_date'])
            //     //     ? "'" . $arr_query_data['end_date'] . "'"
            //     //     : "'" . $existingCred['UserCredentials']['end_date'] . "'",
            //     'UserCredentials.user_id' => !empty($arr_query_data['emp_company_id'])
            //         ? "'" . $arr_query_data['emp_company_id'] . "'"
            //         : "'" . $existingCred['UserCredentials']['user_id'] . "'"
            // ];

            // // Run updateAll
            // $this->UserCredentials->updateAll(
            //     $updateCred,
            //     ['UserCredentials.emp_fkey' => $arr_query_data['emp_fkey']]
            // );

            // 1. Fetch contract record by emp_fkey
            $contractData = $this->EmployeeDetails->query("
    SELECT contracted_days_pkey 
    FROM contracted_days 
    WHERE emp_fkey = '{$emp_fkey}' 
    AND status = 1 
    ORDER BY contracted_days_pkey DESC 
    LIMIT 1
");

            // 2. New End Date from form
            $newEndDate = $arr_query_data['end_date'];

            // 3. Update end_date only if contract row exists
            if (!empty($contractData)) {

                $cd_pkey = $contractData[0]['contracted_days']['contracted_days_pkey'];

                $this->EmployeeDetails->query("
        UPDATE contracted_days
        SET contract_end_date = '{$newEndDate}',
            modified_time = NOW()
        WHERE contracted_days_pkey = '{$cd_pkey}'
    ");
            }

            $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');



            $existingConfigs = $this->EmployeeConfig->find('all', [
                'conditions' => [
                    'emp_fkey' => $emp_fkey,
                    'type IN' => ['GRADE', 'NOTICEPER'],
                    'EmployeeConfig.status'   => 1
                ]
            ]);


            // debug($existingConfigs);
            $this->EmployeeConfig->updateAll(
                ['EmployeeConfig.status' => 0],
                [
                    'EmployeeConfig.emp_fkey' => $emp_fkey,
                    'type IN' => ['GRADE', 'NOTICEPER'],
                    'EmployeeConfig.status'   => 1
                ]
            );


            $arr_emp_config = [
                [
                    'type'              => 'GRADE',
                    'company_code'      => $user,
                    'branch_code'       => null,
                    'emp_fkey'          => $emp_fkey,
                    'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                    'policy_id'         => !empty($arr_query_data['emp_grade']) ? (int)$arr_query_data['emp_grade'] : null,
                    'creation_date'     => date("Y-m-d H:i:s"),
                    'created_by'        => $created_by,
                    'modified_by'       => null,
                    'modification_date' => null,
                    'hirc_leval'        => null,
                    'hirc_up_flag'      => "N",
                    'status'            => 1
                ],
                [
                    'type'              => 'NOTICEPER',
                    'company_code'      => $user,
                    'branch_code'       => null,
                    'emp_fkey'          => $emp_fkey,
                    'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                    'policy_id'         => !empty($arr_query_data['notice_days']) ? (int)$arr_query_data['notice_days'] : null,
                    'creation_date'     => date("Y-m-d H:i:s"),
                    'created_by'        => $created_by,
                    'modified_by'       => null,
                    'modification_date' => null,
                    'hirc_leval'        => null,
                    'hirc_up_flag'      => "N",
                    'status'            => 1
                ]
            ];
            // debug($arr_query_data);
            // debug($arr_emp_config);

            // Save multiple rows
            $this->EmployeeConfig->create();
            $this->EmployeeConfig->saveMany($arr_emp_config);
            echo json_encode(['success' => 'true']);
            return;
        }
        echo json_encode(['status' => 'error']);
    }


    public function savedocument()
    {
        $this->autoRender = false;
        $this->emp_documents->useDbConfig = $this->Session->read('ds');

        if ($this->request->is('post')) {
            $data = $this->request->data;

            // Handle file upload
            if (!empty($_FILES['avatarfile']['name'])) {
                $fileResp = $this->savefileInternal($_FILES['avatarfile']);
                if (!empty($fileResp['error'])) {
                    echo json_encode(['success' => 0, 'msg' => $fileResp['error']]);
                    return;
                }
                $data['file_url'] = $fileResp['data']['avatar'];
            }

            $this->emp_documents->create();
            if ($this->emp_documents->save($data)) {
                echo json_encode(['success' => 1, 'msg' => 'Document saved successfully']);
            } else {
                echo json_encode(['success' => 0, 'msg' => 'Failed to save document']);
            }
        }
    }

    // Reusable upload function
    private function savefileInternal($file)
    {
        $resp = ['error' => '', 'data' => []];
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);

            $allowed = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'pdf'  => 'application/pdf'
            ];

            $ext = array_search($mime, $allowed, true);
            if ($ext === false) {
                throw new RuntimeException('Invalid file format.');
            }

            $uploadDir = WWW_ROOT . 'uploads' . DS . 'documents' . DS;
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = sha1_file($file['tmp_name']) . '.' . $ext;
            $fullPath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }

            $resp['data']['avatar'] = 'uploads/documents/' . $filename;
        } catch (RuntimeException $e) {
            $resp['error'] = $e->getMessage();
        }
        return $resp;
    }

    public function saveconfig()
    {
        $this->autoRender = false;

        // Load DB connections
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

        $empJoinPkey   = isset($this->request->data['emp_fkey']) ? $this->request->data['emp_fkey'] : 0;
        $arr_query_data = $this->request->data;
        // debug($arr_query_data);
        $user = strtoupper($this->Session->read('company_code'));

        $result = $this->EmployeeDetails->query("
        SELECT emp_fkey 
        FROM emp_join
        WHERE emp_join_pkey = '$empJoinPkey'
    ");

        $emp_pkey = !empty($result[0]['emp_join']['emp_fkey']) ? $result[0]['emp_join']['emp_fkey'] : null;

        if (empty($emp_pkey)) {
            echo json_encode([
                'success' => false,
                'message' => 'Need to Save Company details'
            ]);
            return;
        }

        // 🔎 Normalize integer fields
        $shift    = !empty($arr_query_data['shift']) ? (string)(int)$arr_query_data['shift'] : 'NULL';
        // debug($shift);
        $holidays = !empty($arr_query_data['holidays']) ? (string)(int)$arr_query_data['holidays'] : 'NULL';
        $leave    = !empty($arr_query_data['leave']) ? (string)(int)$arr_query_data['leave'] : 'NULL';

        // 🔎 Build updateData
        $updateData = [
            'EmployeeProfessionalDetails.day_time_seq'         => $shift,
            'EmployeeProfessionalDetails.HOLIDAY_GROUP_ID'     => $holidays,
            'EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID' => $leave,

            'EmployeeProfessionalDetails.attr1' => !empty($arr_query_data['hierarch'])
                ? "'" . $arr_query_data['hierarch'] . "'"
                : 'NULL',
            
            'EmployeeProfessionalDetails.attr2' => !empty($arr_query_data['hierarch1'])
                ? "'" . $arr_query_data['hierarch1'] . "'"
                : 'NULL',
        ];

        // Debug before update
        $updated = $this->EmployeeProfessionalDetails->findByEmpFkey($emp_pkey);
        // debug($updateData);
        // debug($updated);

        // 🔎 Run update
        $result = $this->EmployeeProfessionalDetails->updateAll(
            $updateData,
            ['EmployeeProfessionalDetails.emp_fkey' => $emp_pkey]
        );
        // debug($result);
        $user_id = $this->Session->read("login_user_id");
        $created_by = $user_id;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $arr_emp_config = [
            [
                'type'              => 'SHIFT',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => $shift,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ],
            [
                'type'              => 'HOLIDAY',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => $holidays,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ],
            [
                'type'              => 'LEAVE',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => $leave,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ],
             [
                'type'              => 'HIERARCHY',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => $arr_query_data['hierarch'],
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ]
        ];

        // debug($arr_emp_config);

        // Save multiple rows
        $this->EmployeeConfig->create();
        $this->EmployeeConfig->saveMany($arr_emp_config);

        if ($result) {
            echo json_encode([
                'success' => true
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update employee professional details'
            ]);
        }
    }


    public function editconfig()
    {
        $this->autoRender = false;

        // Load DB connections
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        // $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

        $emp_Pkey   = isset($this->request->data['emp_fkey']) ? $this->request->data['emp_fkey'] : 0;
        $arr_query_data = $this->request->data;
        
        // debug($arr_query_data);
        $result = $this->EmployeeDetails->query("
            SELECT emp_pkey 
            FROM emp_details
            WHERE emp_pkey = '$emp_Pkey'
        ");

        $emp_pkey = !empty($arr_query_data['emp_fkey']) ? $arr_query_data['emp_fkey'] : null;

        if (empty($emp_pkey)) {
            echo json_encode([
                'success' => false,
                'message' => 'employee not found'
            ]);
            return;
        }
        $existingData = $this->EmployeeProfessionalDetails->find('first', [
            'conditions' => ['emp_fkey' => $emp_pkey],
            'recursive' => -1
        ]);
        // 🔎 Normalize integer fields
        $shift = !empty($arr_query_data['shift'])
            ? (string)(int)$arr_query_data['shift']
            : $existingData['EmployeeProfessionalDetails']['shift'];

        $holidays = !empty($arr_query_data['holidays'])
            ? (string)(int)$arr_query_data['holidays']
            : $existingData['EmployeeProfessionalDetails']['holidays'];

        $leave = !empty($arr_query_data['leave'])
            ? (string)(int)$arr_query_data['leave']
            : $existingData['EmployeeProfessionalDetails']['leave'];

        $attr1 = (!empty($arr_query_data['hierarch']) && $arr_query_data['hierarch'] !== '')
            ? $arr_query_data['hierarch']
            : (
                isset($existingData['EmployeeProfessionalDetails']['attr1'])
                ? $existingData['EmployeeProfessionalDetails']['attr1']
                : null
            );

        $attr2 = (!empty($arr_query_data['hierarch1']) && $arr_query_data['hierarch1'] !== '')
            ? $arr_query_data['hierarch1']
            : (
                isset($existingData['EmployeeProfessionalDetails']['attr2'])
                ? $existingData['EmployeeProfessionalDetails']['attr2']
                : null
            );
// debug($arr_query_data['hierarch1']);
// debug($attr2);

        $existingConfigs = $this->EmployeeConfig->find('all', [
            'conditions' => [
                'emp_fkey' => $emp_pkey,
                'type IN' => ['LEAVE', 'HOLIDAY', 'SHIFT'],
                'EmployeeConfig.status'   => 1
            ]
        ]);


        // debug($existingConfigs);
        $this->EmployeeConfig->updateAll(
            ['EmployeeConfig.status' => 0],
            [
                'EmployeeConfig.emp_fkey' => $emp_pkey,
                'EmployeeConfig.type IN' => ['SHIFT', 'HOLIDAY', 'LEAVE', 'HIERARCHY'],
                'EmployeeConfig.status'   => 1
            ]
        );


        // 🔎 Build updateData
        $updateData = [
            'EmployeeProfessionalDetails.day_time_seq'         => $shift,
            'EmployeeProfessionalDetails.HOLIDAY_GROUP_ID'     => $holidays,
            'EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID' => $leave,
            'EmployeeProfessionalDetails.attr1' =>  "'" . addslashes($attr1) . "'",
            'EmployeeProfessionalDetails.attr2'    => "'" . addslashes($attr2) . "'",
        ];

        // Debug before update
        $updated = $this->EmployeeProfessionalDetails->findByEmpFkey($emp_pkey);
        // debug($updateData);
        // debug($updated);

        // 🔎 Run update
        $result = $this->EmployeeProfessionalDetails->updateAll(
            $updateData,
            ['EmployeeProfessionalDetails.emp_fkey' => $emp_pkey]
        );
        // debug($result);
        $user = strtoupper($this->Session->read('company_code'));
        $user_id = $this->Session->read("login_user_id");
        $created_by = $user_id;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $arr_emp_config = [
            [
                'type'              => 'SHIFT',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => $shift,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ],
            [
                'type'              => 'HOLIDAY',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => $holidays,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ],
            [
                'type'              => 'LEAVE',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => $leave,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ],[
                'type'              => 'HIERARCHY',
                'company_code'      => $user,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         =>  $attr1,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => $created_by,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ]
        ];

        // debug($arr_emp_config);

        // Save multiple rows
        $this->EmployeeConfig->create();
        $this->EmployeeConfig->saveMany($arr_emp_config);

        //edited by athira 28-11-2025
        $notVerifiedMonths = $this->EmployeeConfig->query("
    SELECT month_year 
    FROM attendance_register
    WHERE emp_fkey = '$emp_pkey'
    AND isdelete = 'Y'
");

// GET BRANCH
$branch_row = $this->EmployeeConfig->query("
    SELECT branch_code FROM emp_details WHERE emp_pkey='$emp_pkey'
");
$branch_code = $branch_row[0]['emp_details']['branch_code'];


    foreach ($notVerifiedMonths as $m) {

        // month_year is like 2025-01
        $monthYear = $m['attendance_register']['month_year'];
        $yearmonth = $monthYear . "-01";


        // GET START + END DATES
        $att_start_row = $this->EmployeeConfig->query("SELECT att_start_end_fn('$yearmonth', 1) AS start_date");
        $att_end_row   = $this->EmployeeConfig->query("SELECT att_start_end_fn('$yearmonth', 2) AS end_date");

        $start_date = $att_start_row[0][0]['start_date'];
        $end_date   = $att_end_row[0][0]['end_date'];

        $current = strtotime($start_date);
        $end     = strtotime($end_date);

        // LOOP THROUGH EACH DAY
        while ($current <= $end) {

            $attDate = date('Y-m-d', $current);

            $result = $this->EmployeeConfig->query("
                SELECT time_duration_check('$attDate', '$emp_pkey', '$branch_code')
            ");

            if (!$result) {
                return false;
            }

            $current = strtotime("+1 day", $current);
        }
    }
    //end


        if ($result) {
            echo json_encode([
                'success' => true
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update employee professional details'
            ]);
        }
    }


    // public function saveconfig()
    // {
    //     $this->autoRender = false;

    //     // Load DB connections
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
    //     $this->UserCredentials->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

    //     $empJoinPkey   = isset($this->request->data['emp_fkey']) ? $this->request->data['emp_fkey'] : 0;
    //     $arr_query_data = $this->request->data;

    //     // 🔎 Step 1: Get emp_pkey from emp_join
    //     $result = $this->EmployeeDetails->query("
    //         SELECT emp_fkey 
    //         FROM emp_join
    //         WHERE emp_join_pkey = '$empJoinPkey'
    //     ");

    //     $emp_pkey = !empty($result[0]['emp_join']['emp_fkey']) ? $result[0]['emp_join']['emp_fkey'] : null;

    //     if (empty($emp_pkey)) {
    //         echo json_encode([
    //             'success' => false,
    //             'message' => 'Invalid employee reference'
    //         ]);
    //         return;
    //     }
    // debug($arr_query_data);
    //     // 🔎 Step 2: Update EmployeeProfessionalDetails
    //     $updateData = [
    //         'EmployeeProfessionalDetails.day_time_seq'         => "'" . $arr_query_data['shift'] . "'",
    //         'EmployeeProfessionalDetails.HOLIDAY_GROUP_ID'     => "'" . $arr_query_data['holidays'] . "'",
    //         'EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID' => "'" . $arr_query_data['leave'] . "'",
    //         'EmployeeProfessionalDetails.attr1'                => "'" . $arr_query_data['hierarch1'] . "'",
    //         'EmployeeProfessionalDetails.attr2'                => "'" . $arr_query_data['hierarch'] . "'"
    //     ];
    // debug($updateData);
    //     $result = $this->EmployeeProfessionalDetails->updateAll(
    //         $updateData,
    //         ['EmployeeProfessionalDetails.emp_fkey' => $emp_pkey]
    //     );

    //     // 🔎 Step 3: Prepare emp_config data
    //     $empConfigData = [
    //         'emp_fkey'     => $emp_pkey,
    //         'policy_id'    => isset($arr_query_data['leave']) ? $arr_query_data['leave'] : null,
    //         'month_year'   => isset($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
    //         'hirc_leval'   => isset($arr_query_data['hirc_level']) ? $arr_query_data['hirc_level'] : null,
    //         'modified_by'  => null,
    //         'modification_date' => date("Y-m-d H:i:s"),
    //         'status'       => 1
    //     ];

    //     // 🔎 Step 4: Check if config exists
    //     $existingConfig = $this->EmployeeConfig->find('first', [
    //         'conditions' => ['EmployeeConfig.emp_fkey' => $emp_pkey]
    //     ]);

    //     if (!empty($existingConfig)) {
    //         // update
    //         $this->EmployeeConfig->id = $existingConfig['EmployeeConfig']['id'];
    //         $this->EmployeeConfig->save($empConfigData);
    //     } else {
    //         // insert
    //         $empConfigData['creation_date'] = date("Y-m-d H:i:s");
    //         $empConfigData['created_by']    = null;
    //         $this->EmployeeConfig->create();
    //         $this->EmployeeConfig->save($empConfigData);
    //     }

    //     if ($result) {
    //         echo json_encode([
    //             'success' => true,
    //             'message' => 'Config and professional details saved successfully'
    //         ]);
    //     } else {
    //         echo json_encode([
    //             'success' => false,
    //             'message' => 'Failed to update employee professional details'
    //         ]);
    //     }
    // }

       public function sendOnboardingMail()
    {
        $this->autoRender = false;

        $empPkey = $this->request->data('emp_fkey');
        $percentage = $this->request->data('percentage');

        $this->loadModel('CentralUserCredentials');
        $this->CentralUserCredentials->setDataSource('controldb');

        $login_user_id = $this->Session->read('login_user_id');

        $adminUser = $this->CentralUserCredentials->find('first', array(
            'conditions' => array('CentralUserCredentials.user_id' => $login_user_id),
            'fields' => array('email', 'first_name', 'last_name'),
            'recursive' => -1
        ));

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $employee = $this->EmployeeDetails->query("
        SELECT
            TRIM(CONCAT(e.first_name, ' ', IFNULL(e.last_name, ''))) AS name,
            p.emp_company_id,
            p.joining_date
        FROM emp_details e
        LEFT JOIN emp_proff p ON p.emp_fkey = e.emp_pkey
        WHERE e.emp_pkey = $empPkey
    ");

        $empName = !empty($employee[0][0]['name']) ? $employee[0][0]['name'] : 'Employee';
        $empId = !empty($employee[0]['p']['emp_company_id']) ? $employee[0]['p']['emp_company_id'] : 'N/A';

        $joiningDate = !empty($employee[0]['p']['joining_date'])
            ? date('d M Y', strtotime($employee[0]['p']['joining_date']))
            : 'N/A';

        $adminName = trim(
            (isset($adminUser['CentralUserCredentials']['first_name']) ? $adminUser['CentralUserCredentials']['first_name'] : '') . ' ' .
            (isset($adminUser['CentralUserCredentials']['last_name']) ? $adminUser['CentralUserCredentials']['last_name'] : '')
        );
        $adminName = $adminName ?: 'Admin';

        $recipientEmail = isset($adminUser['CentralUserCredentials']['email']) ? $adminUser['CentralUserCredentials']['email'] : '';
        $company_code = $this->Session->read('company_code');

        $avatarLetter = strtoupper(substr($empName, 0, 1));
        $percentage = intval($percentage);
        $remaining = 100 - $percentage;
        $year = date('Y');

        // Circular progress ring math (r=28, circumference ≈ 175.9)
        $circumference = 175.9;
        $dashOffset = $circumference - ($percentage / 100) * $circumference;

        if (!empty($recipientEmail)) {

            App::uses('CakeLog', 'Log');
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));

            try {

                $mail = new PHPMailer();

                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
                $mail->Port = 587;
                $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
                $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
                $mail->SMTPSecure = 'tls';
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'html';
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('mypayrollmaster@office24.online', 'My Payroll Master');
                $mail->addAddress($recipientEmail);
                $mail->Subject = "Onboarding Completed " . $empName;
                $mail->isHTML(true);

                $htmlContent = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Onboarding Complete</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:Georgia,serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:40px 0;">
<tr><td align="center">

  <table width="600" cellpadding="0" cellspacing="0"
         style="background:#ffffff;border-radius:16px;overflow:hidden;
                box-shadow:0 8px 40px rgba(0,0,0,0.10);">

    <!-- ═══════════════ HEADER ═══════════════ -->
    <tr>
      <td style="background:linear-gradient(135deg,#0d1b35 0%,#1a3460 60%,#1e4d8c 100%);
                 padding:32px 36px;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <!-- Logo -->
            <td width="56" valign="middle">
              <img src="https://v1.mypayrollmaster.online/img/rizo.png"
                   width="50" height="50"
                   alt="MyPayrollMaster Logo"
                   style="display:block;border-radius:10px;
                          border:2px solid rgba(255,255,255,0.25);
                          box-shadow:0 4px 12px rgba(0,0,0,0.3);">
            </td>
            <!-- Brand Name -->
            <td valign="middle" style="padding-left:14px;">
              <div style="color:#ffffff;font-size:20px;font-weight:bold;
                          letter-spacing:0.5px;line-height:1.1;">
               Rizo
             
            </td>
            <!-- Badge -->
            <td align="right" valign="middle">
              <span style="background:rgba(255,255,255,0.12);
                           border:1px solid rgba(255,255,255,0.25);
                           color:#a8d8ff;font-size:11px;
                           letter-spacing:1px;text-transform:uppercase;
                           padding:5px 12px;border-radius:20px;
                           font-family:Arial,sans-serif;">
                Onboarding
              </span>
            </td>
          </tr>
        </table>

        <!-- Divider -->
        <div style="height:1px;background:rgba(255,255,255,0.12);margin:22px 0 18px;"></div>

        <!-- Hero Title -->
        <div style="text-align:center;">
          <div style="font-size:28px;color:#ffffff;font-weight:bold;
                      letter-spacing:-0.5px;">
            &#x2713;&nbsp; Onboarding Complete!
          </div>
          <div style="color:rgba(255,255,255,0.65);font-size:13px;
                      margin-top:6px;font-family:Arial,sans-serif;">
            All steps have been successfully recorded
          </div>
        </div>
      </td>
    </tr>

    <!-- ═══════════════ EMPLOYEE CARD ═══════════════ -->
    <tr>
      <td style="padding:32px 36px 20px;">

        <p style="margin:0 0 22px;font-size:15px;color:#444;
                  font-family:Arial,sans-serif;line-height:1.6;">
          Hi <strong style="color:#0d1b35;">' . $adminName . '</strong>,<br>
          The following employee has been <strong>successfully onboarded</strong>
          into the system. Here is a summary of the details:
        </p>

        <!-- Employee Info Card -->
        <table width="100%" cellpadding="0" cellspacing="0"
               style="background:linear-gradient(135deg,#f4f8ff,#eaf0fb);
                      border-radius:12px;overflow:hidden;
                      border:1px solid #d6e4f7;">
          <tr>
            <!-- Avatar Column -->
            <td width="90" valign="top"
                style="padding:24px 0 24px 24px;">
              <div style="width:60px;height:60px;border-radius:50%;
                          background:linear-gradient(135deg,#1a3460,#1e6abf);
                          color:#fff;font-size:26px;font-weight:bold;
                          text-align:center;line-height:60px;
                          font-family:Georgia,serif;
                          box-shadow:0 4px 14px rgba(30,74,143,0.35);">
                ' . $avatarLetter . '
              </div>
            </td>
            <!-- Info Column -->
            <td valign="top" style="padding:22px 24px;">
              <div style="font-size:18px;font-weight:bold;
                          color:#0d1b35;font-family:Georgia,serif;">
                ' . $empName . '
              </div>
              <div style="height:1px;background:#d0dff0;margin:10px 0;"></div>
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="font-size:12px;color:#7a90aa;
                              font-family:Arial,sans-serif;
                              text-transform:uppercase;letter-spacing:0.8px;
                              padding-bottom:5px;padding-right:16px;">
                    Employee ID
                  </td>
                  <td style="font-size:12px;color:#7a90aa;
                              font-family:Arial,sans-serif;
                              text-transform:uppercase;letter-spacing:0.8px;
                              padding-bottom:5px;padding-right:16px;">
                    Joining Date
                  </td>
                  <td style="font-size:12px;color:#7a90aa;
                              font-family:Arial,sans-serif;
                              text-transform:uppercase;letter-spacing:0.8px;
                              padding-bottom:5px;">
                    Company
                  </td>
                </tr>
                <tr>
                  <td style="font-size:14px;font-weight:bold;color:#1a3460;
                              font-family:Arial,sans-serif;padding-right:16px;">
                    ' . $empId . '
                  </td>
                  <td style="font-size:14px;font-weight:bold;color:#1a3460;
                              font-family:Arial,sans-serif;padding-right:16px;">
                    ' . $joiningDate . '
                  </td>
                  <td style="font-size:14px;font-weight:bold;color:#1a3460;
                              font-family:Arial,sans-serif;">
                    ' . $company_code . '
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    <!-- ═══════════════ PROGRESS SECTION ═══════════════ -->
    <tr>
      <td style="padding:10px 36px 28px;">
        <table width="100%" cellpadding="0" cellspacing="0"
               style="background:#0d1b35;border-radius:12px;overflow:hidden;">
          <tr>
            <!-- SVG Ring -->
            <td width="110" align="center" style="padding:24px 0 24px 24px;">
              <svg width="80" height="80" viewBox="0 0 80 80"
                   xmlns="http://www.w3.org/2000/svg">
                <!-- Track -->
                <circle cx="40" cy="40" r="28"
                        fill="none" stroke="rgba(255,255,255,0.1)"
                        stroke-width="7"/>
                <!-- Progress arc -->
                <circle cx="40" cy="40" r="28"
                        fill="none" stroke="#5cb8ff"
                        stroke-width="7"
                        stroke-linecap="round"
                        stroke-dasharray="' . $circumference . '"
                        stroke-dashoffset="' . $dashOffset . '"
                        transform="rotate(-90 40 40)"/>
                <!-- Percentage text -->
                <text x="40" y="45"
                      text-anchor="middle"
                      font-size="16" font-weight="bold"
                      fill="#ffffff"
                      font-family="Arial,sans-serif">
                  ' . $percentage . '%
                </text>
              </svg>
            </td>
            <!-- Label -->
            <td valign="middle" style="padding:24px;">
              <div style="color:#5cb8ff;font-size:11px;
                          text-transform:uppercase;letter-spacing:1.5px;
                          font-family:Arial,sans-serif;margin-bottom:5px;">
                Profile Completion
              </div>
              <div style="color:#ffffff;font-size:22px;font-weight:bold;
                          font-family:Georgia,serif;">
                ' . $percentage . '% Complete
              </div>
              <div style="color:rgba(255,255,255,0.5);font-size:12px;
                          font-family:Arial,sans-serif;margin-top:4px;">
                ' . $remaining . '% of profile fields still pending
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- ═══════════════ FOOTER ═══════════════ -->
    <tr>
      <td style="background:#f4f7fb;border-top:1px solid #dde8f5;padding:28px 36px;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <!-- Logo + Brand (inline) -->
            <td valign="middle">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td valign="middle">
                    <img src="https://v1.mypayrollmaster.online/img/rizo.png"
                         width="36" height="36"
                         alt="Logo"
                         style="display:block;border-radius:7px;
                                border:1px solid #d0dce8;">
                  </td>
                  <td valign="middle" style="padding-left:10px;">
                    <div style="font-size:14px;font-weight:bold;
                                color:#0d1b35;font-family:Arial,sans-serif;">
                     Rizo
                    </div>
                    
                  </td>
                </tr>
              </table>
            </td>
            <!-- Copyright -->
            <td align="right" valign="middle">
              <div style="font-size:11px;color:#aabbcc;
                          font-family:Arial,sans-serif;line-height:1.6;">
                &copy; ' . $year . ' My Payroll Master.<br>
                <span style="color:#c5d5e8;">All rights reserved.</span>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>

</td></tr>
</table>

</body>
</html>';

                $mail->MsgHTML($htmlContent);
                $mail->AltBody = "Onboarding completed for $empName ($percentage% profile completion). Employee ID: $empId, Joining Date: $joiningDate, Company: $company_code.";

                if (!$mail->send()) {
                    CakeLog::write('error', 'Mail Error: ' . $mail->ErrorInfo);
                    echo json_encode(array('success' => false, 'error' => $mail->ErrorInfo));
                } else {
                    echo json_encode(array('success' => true));
                }

            } catch (Exception $e) {
                echo json_encode(array('success' => false, 'error' => $e->getMessage()));
            }

        } else {
            echo json_encode(array('success' => false, 'error' => 'Email not found'));
        }
    }
    public function calculateOnboardingPercentage($emp_pkey)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $isFilled = function ($v) {
            return isset($v) && trim((string) $v) !== '' && $v !== null && (string) $v !== '0';
        };

        // 1. Personal Data
        $personalFields = [
            'first_name',
            'date_of_birth',
            'classification',
            'email',
            'mobile_no',
            'address',
            'pincode',
            'nationality_id',
            'city',
            'state',
            'maritual_status',
            'guradian',
            'relation_guardian',
            'blood',
            'id_card',
            'pan_no',
            'bank_name',
            'branch_name',
            'ifsc_code',
            'account_no',
            'esi_dispensary',
            'esi',
            'pf',
            'company_pf',
            'previous_member_id',
            'wps_code',
            'lwf_code',
            'profile_pic',
            'international_worker',
            'country'
        ];

        $personalSql = "SELECT " . implode(',', $personalFields) . " FROM emp_details WHERE emp_pkey = '$emp_pkey' LIMIT 1";
        $results = $this->EmployeeDetails->query($personalSql);
        $empData = !empty($results) ? $results[0] : [];

        $pTotal = count($personalFields);
        $pFilled = 0;
        $isInternational = false;

        if (!empty($empData)) {
            $data = isset($empData['emp_details']) ? $empData['emp_details'] : (isset($empData['EmployeeDetails']) ? $empData['EmployeeDetails'] : []);
            $isInternational = isset($data['international_worker']) && strtolower(trim((string) $data['international_worker'])) === 'y';

            foreach ($personalFields as $f) {
                if ($f === 'country' && !$isInternational) {
                    $pTotal--;
                    continue;
                }
                $val = isset($data[$f]) ? $data[$f] : '';
                if ($f === 'profile_pic') {
                    if ($isFilled($val) && strpos($val, 'placeholdermen.jpeg') === false && strpos($val, 'placeholderwomen.jpeg') === false) {
                        $pFilled++;
                    }
                } else {
                    if ($isFilled($val))
                        $pFilled++;
                }
            }
        }
        $personalPercent = $pTotal > 0 ? round(($pFilled / $pTotal) * 100) : 100;

        // 2. Company Data
        $baseFields = ['joining_date', 'emp_branch', 'emp_dept', 'designation', 'emp_type', 'notice_days'];
        $proData = $this->EmployeeDetails->query("SELECT joining_date, emp_branch, emp_dept, designation, emp_type, notice_days, emp_grade, probation FROM emp_proff WHERE emp_fkey = '$emp_pkey' LIMIT 1");

        $cFilled = 0;
        $cTotal = count($baseFields);
        $companyPercent = 0;
        if (!empty($proData)) {
            $proRow = $proData[0]['emp_proff'];
            $empType = trim(strtolower((string) $proRow['emp_type']));
            foreach ($baseFields as $bf) {
                if ($isFilled($proRow[$bf]))
                    $cFilled++;
            }
            if ($empType === 'permanent') {
                $cTotal++;
                if ($isFilled($proRow['emp_grade']))
                    $cFilled++;
            } elseif ($empType === 'contract') {
                $contractData = $this->EmployeeDetails->query("SELECT contract_start_date, contract_end_date FROM contracted_days WHERE emp_fkey = '$emp_pkey' AND status = 1 LIMIT 1");
                $cTotal += 2;
                if (!empty($contractData)) {
                    if ($isFilled($contractData[0]['contracted_days']['contract_start_date']))
                        $cFilled++;
                    if ($isFilled($contractData[0]['contracted_days']['contract_end_date']))
                        $cFilled++;
                }
            } elseif ($empType === 'probation') {
                $cTotal++;
                if ($isFilled($proRow['probation']))
                    $cFilled++;
            }
            $companyPercent = ($cTotal > 0) ? round(($cFilled / $cTotal) * 100) : 100;
        }

        // 3. Family
        $famCount = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM emp_family WHERE emp_fkey = '$emp_pkey' and status = 1");
        if (empty($famCount[0][0]['total'])) {
            $famCount = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM family WHERE emp_join_fkey = '$emp_pkey' and status = 1");
        }
        $famPercent = (isset($famCount[0][0]['total']) && $famCount[0][0]['total'] > 0) ? 100 : 0;

        // 4. Education / Qualifications
        $qualCount = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM qualifcations WHERE emp_fkey = '$emp_pkey' and status = 1");
        if (empty($qualCount[0][0]['total'])) {
            $qualCount = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM Education WHERE emp_join_fkey = '$emp_pkey' and status = 1");
        }
        $eduPercent = (isset($qualCount[0][0]['total']) && $qualCount[0][0]['total'] > 0) ? 100 : 0;

        // 5. Work Experience / History
        $histCount = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM history WHERE emp_fkey = '$emp_pkey' and status = 1");
        if (empty($histCount[0][0]['total'])) {
            $histCount = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM work_experience WHERE emp_join_fkey = '$emp_pkey' and status = 1");
        }
        $workPercent = (isset($histCount[0][0]['total']) && $histCount[0][0]['total'] > 0) ? 100 : 0;

        // 6. Config
        $configData = $this->EmployeeDetails->query("
    SELECT COUNT(DISTINCT type) AS total
    FROM emp_config
    WHERE emp_fkey = '$emp_pkey'
    AND status = 1
    AND type IN ('SHIFT', 'HOLIDAY', 'LEAVE', 'HIERARCHY')
");

$configCount = isset($configData[0][0]['total'])
    ? (int)$configData[0][0]['total']
    : 0;

$totalRequired = 4;
$configPercent = round(($configCount / $totalRequired) * 100);

        // 7. Documents
        $docData = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM emp_passport_visa WHERE emp_fkey = '$emp_pkey' AND status = 1");
        if (empty($docData[0][0]['total'])) {
            $docData = $this->EmployeeDetails->query("SELECT COUNT(*) AS total FROM emp_documents WHERE emp_join_fkey = '$emp_pkey' AND status = 1");
        }
        $docPercent = (isset($docData[0][0]['total']) && $docData[0][0]['total'] > 0) ? 100 : 0;

        // Calculation: Simple average of 7 sections (Personal, Company, Education, Family, Work, Documents, Config)
        return round(($personalPercent + $companyPercent + $eduPercent + $famPercent + $workPercent + $docPercent + $configPercent) / 7);
    }


public function getOnboardingCompletion()
{
    $this->autoRender = false;
    $this->response->type('json');

    $empJoinPkey = isset($this->request->data['emp_fkey'])
        ? (int)$this->request->data['emp_fkey']
        : 0;

    if (!$empJoinPkey) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Missing emp_fkey'
        ));
        return;
    }

    $ds = $this->Session->read('ds');
    $this->EmployeeDetails->useDbConfig = $ds;
    $this->Education->useDbConfig = $ds;
    $this->Family->useDbConfig = $ds;
    $this->WorkExperience->useDbConfig = $ds;

    $empJoinData = $this->EmployeeDetails->query(
        "SELECT * FROM emp_join WHERE emp_join_pkey = $empJoinPkey LIMIT 1"
    );

    if (empty($empJoinData)) {
        echo json_encode(array(
            'success' => false,
            'message' => 'No emp_join record found'
        ));
        return;
    }

    $join = $empJoinData[0]['emp_join'];

    $isFilled = function ($value) {
        return isset($value) &&
            trim((string)$value) !== '' &&
            $value !== null &&
            (string)$value !== '0';
    };

    /* =====================================================
     * 1. PERSONAL INFORMATION
     * ===================================================== */
    $mandatoryFields = array(
        'first_name', 'date_of_birth', 'email', 'classification',
        'mobile_no', 'address', 'pincode', 'district', 'maritual_status',
        'nationality_id', 'state', 'guradian', 'blood', 'id_card',
        'relation_guardian', 'bank', 'pan_no', 'bank_branch', 'ifsc_code',
        'account_no', 'pf', 'esi', 'company_pf', 'previous_member_id',
        'esi_dispensary', 'wps_code', 'lwf_code', 'profile_image_url'
    );

    $pTotal  = count($mandatoryFields);
    $pFilled = 0;

    foreach ($mandatoryFields as $field) {
        $value = isset($join[$field]) ? $join[$field] : '';
        if ($field === 'profile_image_url') {
            if ($isFilled($value) &&
                strpos($value, 'placeholdermen.jpeg') === false &&
                strpos($value, 'placeholderwomen.jpeg') === false) {
                $pFilled++;
            }
        } else {
            if ($isFilled($value)) $pFilled++;
        }
    }

    /* =====================================================
     * 2. OPTIONAL CONDITIONAL FIELDS
     * ===================================================== */
    $epsValue = isset($join['eps']) ? strtolower(trim($join['eps'])) : '';
    if (in_array($epsValue, array('y', 'yes', '1', 'true'))) {
        $pTotal++;
        $pFilled++;
    }

    $isInternational = isset($join['international_worker']) &&
        strtolower(trim($join['international_worker'])) == 'y';
    if ($isInternational) {
        $pTotal++;
        if ($isFilled(isset($join['country_origin']) ? $join['country_origin'] : '')) {
            $pFilled++;
        }
    }

    $isHandicap = isset($join['physical_handicap']) &&
        strtolower(trim($join['physical_handicap'])) == 'y';
    if ($isHandicap) {
        $pTotal++;
        $locomotive = isset($join['locomotive']) ? strtolower($join['locomotive']) : '';
        $hearing    = isset($join['hearing'])    ? strtolower($join['hearing'])    : '';
        $visual     = isset($join['visual'])     ? strtolower($join['visual'])     : '';
        if ($locomotive == 'y' || $hearing == 'y' || $visual == 'y') {
            $pFilled++;
        }
    }

    $personalPercent = ($pTotal > 0) ? round(($pFilled / $pTotal) * 100) : 0;

    /* =====================================================
     * 3. COMPANY INFORMATION
     * ===================================================== */
    $getValue = function ($key) {
        return isset($this->request->data[$key])
            ? trim($this->request->data[$key])
            : '';
    };

    $baseFields = array(
        'joining_date', 'emp_branch', 'emp_dept',
        'designation', 'emp_type', 'notice_days'
    );

    $cTotal  = count($baseFields);
    $cFilled = 0;

    foreach ($baseFields as $field) {
        if ($isFilled($getValue($field))) $cFilled++;
    }

    $empType = strtolower($getValue('emp_type'));

    if ($empType === 'permanent') {
        $cTotal++;
        if ($isFilled($getValue('emp_grade'))) $cFilled++;

    } elseif ($empType === 'probation') {
        $cTotal++;
        if ($isFilled($getValue('probation'))) $cFilled++;   // HTML name="probation"

    } elseif ($empType === 'contract') {
        $cTotal += 2;
        if ($isFilled($getValue('start_date'))) $cFilled++;  // ✅ was contract_start_date
        if ($isFilled($getValue('end_date')))   $cFilled++;  // ✅ was contract_end_date
    }

    $companyPercent = ($cTotal > 0) ? round(($cFilled / $cTotal) * 100) : 0;

    /* =====================================================
     * 4. EDUCATION
     * ===================================================== */
    $eduData = $this->EmployeeDetails->query(
        "SELECT * FROM Education WHERE emp_join_fkey = $empJoinPkey LIMIT 1"
    );
    $eduPercent = 0;
    if (!empty($eduData)) {
        $f      = $eduData[0]['Education'];
        $fields = array('degree', 'specialization', 'university', 'year_of_passing');
        $filled = 0;
        foreach ($fields as $k) {
            if ($isFilled(isset($f[$k]) ? $f[$k] : '')) $filled++;
        }
        $eduPercent = round(($filled / count($fields)) * 100);
    }

    /* =====================================================
     * 5. FAMILY
     * ===================================================== */
    $famData = $this->EmployeeDetails->query(
        "SELECT * FROM family WHERE emp_join_fkey = $empJoinPkey LIMIT 1"
    );
    $famPercent = 0;
    if (!empty($famData)) {
        $f      = $famData[0]['family'];
        $fields = array('full_name', 'relationship', 'dob', 'contact_number');
        $filled = 0;
        foreach ($fields as $k) {
            if ($isFilled(isset($f[$k]) ? $f[$k] : '')) $filled++;
        }
        $famPercent = round(($filled / count($fields)) * 100);
    }

    /* =====================================================
     * 6. WORK EXPERIENCE
     * ===================================================== */
    $workData = $this->EmployeeDetails->query(
        "SELECT * FROM work_experience WHERE emp_join_fkey = $empJoinPkey LIMIT 1"
    );
    $workPercent = 0;
    if (!empty($workData)) {
        $f      = $workData[0]['work_experience'];
        $fields = array('company_name', 'designation', 'from_date', 'to_date');
        $filled = 0;
        foreach ($fields as $k) {
            if ($isFilled(isset($f[$k]) ? $f[$k] : '')) $filled++;
        }
        $workPercent = round(($filled / count($fields)) * 100);
    }

    /* =====================================================
     * 7. DOCUMENTS
     * ===================================================== */
    $docData = $this->EmployeeDetails->query(
        "SELECT COUNT(*) AS total FROM emp_documents WHERE emp_join_fkey = $empJoinPkey"
    );
    $docCount   = isset($docData[0][0]['total']) ? (int)$docData[0][0]['total'] : 0;
    $docPercent = ($docCount > 0) ? 100 : 0;

    /* =====================================================
     * FINAL RESPONSE
     * ===================================================== */
    echo json_encode(array(
        'success'          => true,

        'personal_percent' => $personalPercent,
        'personal_filled'  => $pFilled,
        'personal_total'   => $pTotal,

        'company_percent'  => $companyPercent,
        'company_filled'   => $cFilled,
        'company_total'    => $cTotal,

        'education_percent' => $eduPercent,
        'education_records' => count($eduData),

        'family_percent'   => $famPercent,
        'family_records'   => count($famData),

        'work_percent'     => $workPercent,
        'work_records'     => count($workData),

        'document_percent' => $docPercent,
        'document_records' => $docCount
    ));
}

    public function saveonboarding()
    {
        $this->autoRender = false;


        // Set DB configs
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
        // 🔹 Always use emp_join_pkey from form
        $empJoinPkey = isset($this->request->data['emp_fkey']) ? $this->request->data['emp_fkey'] : 0;
        // debug($empJoinPkey);
        $arr_query_data = $this->request->data;
        // debug($arr_query_data);
        $company_code = $this->Session->read('company_code');
        // debug($company_code);

        // 🔹 Step 1: Fetch employee join data
        $empJoinData = $this->EmployeeDetails->query("
        SELECT * FROM emp_join
        WHERE emp_join_pkey = '$empJoinPkey'
    ");
        // debug($empJoinData);
        if (empty($empJoinData)) {
            return json_encode([
                'success' => false,
                'error'   => 'No join details record found.'
            ]);
        }

        $join = $empJoinData[0]['emp_join'];
        $emp_pkey = !empty($join['emp_fkey']) ? $join['emp_fkey'] : 0;

        $empJoinData = $this->EmployeeDetails->query("
    SELECT * FROM emp_join WHERE emp_join_pkey = '$empJoinPkey'
");

        if (empty($empJoinData)) {
            return json_encode([
                'success' => false,
                'message' => 'No emp_join record found.'
            ]);
        }

        // FIXED: Correct index
        $join = $empJoinData[0]['emp_join'];

        // FIXED: Mandatory fields
        $missing = [];
        if (empty($join['first_name'])) $missing[] = 'First Name';
        if (empty($join['classification'])) $missing[] = 'Gender';
        if (empty($join['date_of_birth'])) $missing[] = 'Date of Birth';
        if (empty($join['nationality_id'])) $missing[] = 'Nationality';
        if (empty($join['id_card'])) $missing[] = 'Aadhaar Number';
        // if (empty($join['pan_no'])) $missing[] = 'PAN Number';
        
        if (!empty($missing)) {
            return json_encode([
                'success' => false,
                'message' => 'Please fill and save mandatory fields: ' . implode(', ', $missing)
            ]);
        }

        // AGE CHECK
        $today = date('Y-m-d');
        $age = date_diff(date_create($join['date_of_birth']), date_create($today))->y;

        if ($age < 18) {
            return json_encode([
                'success' => false,
                'message' => 'Employee must be at least 18 years old.'
            ]);
        }
        //---------------------------------------
// 🔹 Step: DUPLICATE VALIDATION
//---------------------------------------
$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

// PAN CHECK
if (!empty($join['pan_no'])) {
    $panExists = $this->EmployeeDetails->find('first', [
        'conditions' => [
            'EmployeeDetails.pan_no' => $join['pan_no'],
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.emp_pkey !=' => $emp_pkey
        ]
    ]);

    if (!empty($panExists)) {
        return json_encode([
            'success' => false,
            'message' => 'PAN number already exists for another employee.'
        ]);
    }
}

// AADHAAR CHECK
if (!empty($join['id_card'])) {
    $aadhaarExists = $this->EmployeeDetails->find('first', [
        'conditions' => [
            'EmployeeDetails.id_card' => $join['id_card'],
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.emp_pkey !=' => $emp_pkey
        ]
    ]);

    if (!empty($aadhaarExists)) {
        return json_encode([
            'success' => false,
            'message' => 'Aadhaar number already exists for another employee.'
        ]);
    }
}

// ESI CHECK
if (!empty($join['esi'])) {
    $esiExists = $this->EmployeeDetails->find('first', [
        'conditions' => [
            'EmployeeDetails.esi' => $join['esi'],
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.emp_pkey !=' => $emp_pkey
        ]
    ]);

    if (!empty($esiExists)) {
        return json_encode([
            'success' => false,
            'message' => 'ESI number already exists for another employee.'
        ]);
    }
}

// UAN CHECK
if (!empty($join['company_pf'])) {
    $uanExists = $this->EmployeeDetails->find('first', [
        'conditions' => [
            'EmployeeDetails.pf' => $join['company_pf'],
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.emp_pkey !=' => $emp_pkey
        ]
    ]);

    if (!empty($uanExists)) {
        return json_encode([
            'success' => false,
            'message' => 'UAN already exists for another employee.'
        ]);
    }
}

// LWF CODE CHECK
if (!empty($join['lwf_code'])) {
    $lwfExists = $this->EmployeeDetails->find('first', [
        'conditions' => [
            'EmployeeDetails.lwf_code' => $join['lwf_code'],
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.emp_pkey !=' => $emp_pkey
        ]
    ]);

    if (!empty($lwfExists)) {
        return json_encode([
            'success' => false,
            'message' => 'LWF code already exists for another employee.'
        ]);
    }
}

// ===== PF CHECK =====
if (!empty($join['pf'])) {
    $pfExists = $this->EmployeeDetails->find('first', [
        'conditions' => [
            'EmployeeDetails.company_pf' => $join['pf'],
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.emp_pkey !=' => $emp_pkey
        ]
    ]);

    if (!empty($pfExists)) {
        return json_encode([
            'success' => false,
            'message' => 'PF number already exists for another employee.'
        ]);
    }
}

// ===== ACCOUNT NUMBER CHECK =====
if (!empty($join['account_no'])) {
    $accountExists = $this->EmployeeDetails->find('first', [
        'conditions' => [
            'EmployeeDetails.account_no' => $join['account_no'],
            'EmployeeDetails.status' => 1,
            'EmployeeDetails.emp_pkey !=' => $emp_pkey
        ]
    ]);

    if (!empty($accountExists)) {
        return json_encode([
            'success' => false,
            'message' => 'Account number already exists for another employee.'
        ]);
    }
}

        // 🔹 Step 2: Build emp_details array from emp_join
        $arr_form_data = [];
        $arr_form_data['emp_pkey']           = $emp_pkey;
        $arr_form_data['first_name'] = isset($join['first_name']) ? $join['first_name'] : '';
        $arr_form_data['last_name']  = isset($join['last_name']) ? $join['last_name'] : '';
        $arr_form_data['date_of_birth']      = $join['date_of_birth'];
        $arr_form_data['email']              = $join['email'];
        $arr_form_data['mobile_no']          = $join['mobile_no'];
        $arr_form_data['address']            = $join['address'];
        $arr_form_data['city']            = $join['district'];
        $arr_form_data['id_card']            = $join['id_card'];
        $arr_form_data['pincode']            = $join['pincode'];
        $arr_form_data['blood']              = $join['blood'];
        $arr_form_data['maritual_status']    = $join['maritual_status'];
        $arr_form_data['guradian']           = $join['guradian'];
        $arr_form_data['relation_guardian']  = $join['relation_guardian'];
        $arr_form_data['classification'] =  ($join['classification'] === 'others') ? 'Other' : $join['classification'];
        $arr_form_data['nationality_id']     = $join['nationality_id'];
        $arr_form_data['state']              = $join['state'];
        $arr_form_data['bank_name']          = $join['bank'];
        $arr_form_data['branch_name']        = $join['bank_branch'];
        $arr_form_data['ifsc_code']          = $join['ifsc_code'];
        $arr_form_data['account_no']         = $join['account_no'];
        $arr_form_data['company_pf']                 = $join['pf'];
        $arr_form_data['pf']         = $join['company_pf'];
        $arr_form_data['previous_member_id'] = $join['previous_member_id'];
        $arr_form_data['esi_dispensary']     = $join['esi_dispensary'];
        $arr_form_data['international_worker'] = $join['international_worker'];
        $arr_form_data['locomotive']         = $join['locomotive'];
        $arr_form_data['hearing']            = $join['hearing'];
        $arr_form_data['visual']             = $join['visual'];
        $arr_form_data['country']            = $join['country_origin'];
        $arr_form_data['wps_code']           = $join['wps_code'];
        $arr_form_data['lwf_code']           = $join['lwf_code'];
        $arr_form_data['status']             = $join['status'];
        $arr_form_data['physical_handicap']  = $join['physical_handicap'];
        $arr_form_data['esi']                = $join['esi'];
        $arr_form_data['eps']                = $join['eps'];
        $arr_form_data['pan_no']             = $join['pan_no'];
        $arr_form_data['editable'] =0;
        $arr_form_data['profile_pic']        = !empty($join['profile_image_url'])
            ? $join['profile_image_url']
            : 'img/placeholdermen.jpeg';
        // $arr_form_data['company_code'] = isset($arr_form_data['company_code']) && $arr_form_data['company_code'] !== '' 
        //     ? $arr_form_data['company_code'] 
        //     : (isset($arr_query_data['company_code']) ? $arr_query_data['company_code'] : '');

        $arr_form_data['company_code'] = $company_code;
        $arr_form_data['branch_code']        = $arr_query_data['emp_branch'];

        // 🔹 Step 4: Merge professional details from form (if any)
        if (!empty($this->request->data['professional'])) {
            $arr_form_data = array_merge($arr_form_data, $this->request->data['professional']);
        }

        // 🔹 Step 5: Add created_by / modified_by
        if ($emp_pkey == 0) {
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        } else {
            $arr_form_data['modified_by']  = $this->Session->read('login_user_id');
            $arr_form_data['modified_date'] = date("Y-m-d H:i:s");
        }
        // debug($arr_form_data);
        try {
            // 🔹 Step 6: Save emp_details
            $result = $this->EmployeeDetails->save($arr_form_data);

            if (!empty($result['EmployeeDetails']['emp_pkey'])) {
                $emp_pkey = $result['EmployeeDetails']['emp_pkey'];

                // 🔹 Step 7: Update emp_join with new emp_pkey
                $this->EmployeeDetails->query("
                UPDATE emp_join
                SET emp_fkey = $emp_pkey
                WHERE emp_join_pkey = $empJoinPkey
            ");
            }
        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'error'   => $e->getMessage()
            ]);
        }


        $arr_professional_data = [];

        // Required foreign key
        $arr_professional_data['emp_fkey'] = $emp_pkey;

        // Basic info from form / payload
        $arr_professional_data['joining_date'] = isset($arr_query_data['joining_date']) ? $arr_query_data['joining_date'] : null;
        $arr_professional_data['emp_company_id'] = (isset($arr_query_data['emp_company_id']) && (trim($arr_query_data['emp_company_id']) != '')) ? $arr_query_data['emp_company_id'] : null;
        $arr_professional_data['emp_type'] = isset($arr_query_data['emp_type']) ? $arr_query_data['emp_type'] : null;
        $arr_professional_data['designation'] = isset($arr_query_data['designation']) ? $arr_query_data['designation'] : null;
        $arr_professional_data['section'] = isset($arr_query_data['section']) ? $arr_query_data['section'] : null;
        $arr_professional_data['division'] = isset($arr_query_data['division']) ? $arr_query_data['division'] : null;
        $arr_professional_data['emp_dept'] = isset($arr_query_data['emp_dept']) ? $arr_query_data['emp_dept'] : null;
        $arr_professional_data['emp_grade'] = isset($arr_query_data['emp_grade']) ? $arr_query_data['emp_grade'] : null;
        $arr_professional_data['emp_vertical'] = isset($arr_query_data['division']) ? $arr_query_data['division'] : null; // vertical = division
        $arr_professional_data['emp_branch'] = isset($arr_query_data['emp_branch']) ? $arr_query_data['emp_branch'] : null;
        $arr_professional_data['notice_days'] = isset($arr_query_data['notice_days']) ? $arr_query_data['notice_days'] : null;
        $arr_professional_data['emp_grade'] = isset($arr_query_data['emp_grade']) ? $arr_query_data['emp_grade'] : null;
        $arr_professional_data['probation'] = isset($arr_query_data['probation']) ? $arr_query_data['probation'] : null;

        // Leave & holiday related
        $arr_professional_data['HOLIDAY_GROUP_ID'] = isset($arr_query_data['HOLIDAY_GROUP_ID']) ? $arr_query_data['HOLIDAY_GROUP_ID'] : null;
        $arr_professional_data['LEAVEPOLICY_GROUP_ID'] = isset($arr_query_data['LEAVEPOLICY_GROUP_ID']) ? $arr_query_data['LEAVEPOLICY_GROUP_ID'] : null;

        // Privileges
        $arr_professional_data['emp_sep_priv'] = isset($arr_query_data['section']) ? $arr_query_data['section'] : null; // section field
        $arr_professional_data['emp_mgt_priv'] = isset($arr_query_data['previous_employer_gratuity']) ? $arr_query_data['previous_employer_gratuity'] : 0;

        // Other fields
        $arr_professional_data['created_by'] = $this->Session->read('login_user_id');
        $arr_professional_data['creation_date'] = date("Y-m-d H:i:s");

        // Optional attrs if present
        for ($i = 1; $i <= 6; $i++) {
            $attr = 'attr' . $i;
            if (isset($arr_query_data[$attr])) {
                $arr_professional_data[$attr] = $arr_query_data[$attr];
            }
        }
          // 🔹 Step: Validate Employee Company ID uniqueness
        $emp_company_id = isset($arr_query_data['emp_company_id']) ? $arr_query_data['emp_company_id'] : '';
        if (!empty($emp_company_id)) {
            $existingCompanyId = $this->EmployeeProfessionalDetails->find('first', [
                'conditions' => [
                    'EmployeeProfessionalDetails.emp_company_id' => $emp_company_id,
                    'EmployeeProfessionalDetails.emp_fkey !=' => $emp_pkey
                ],
                'recursive' => -1
            ]);

            if (!empty($existingCompanyId)) {
                $existing_emp_fkey = $existingCompanyId['EmployeeProfessionalDetails']['emp_fkey'];
                $empNameData = $this->EmployeeDetails->find('first', [
                    'conditions' => ['EmployeeDetails.emp_pkey' => $existing_emp_fkey],
                    'fields' => ['first_name', 'last_name'],
                    'recursive' => -1
                ]);

                $emp_name = !empty($empNameData) ? trim($empNameData['EmployeeDetails']['first_name'] . ' ' . $empNameData['EmployeeDetails']['last_name']) : 'Unknown';
            $this->restsave($emp_pkey, $empJoinPkey);
                return json_encode([
                    'success' => false,
                    'message' => "Employee Company ID already exists. Employee Name: " . $emp_name
                ]);
            }
        }


        $arr_user_cred = [];

        $arr_user_cred['emp_fkey']       = isset($emp_pkey) ? $emp_pkey : null;
        $arr_user_cred['company_code']   = isset($company_code) ? $company_code : null;
        $arr_user_cred['user_id']        = '0';
        $arr_user_cred['password']       = isset($arr_form_data['password']) ? $arr_form_data['password'] : null;
        $arr_user_cred['access_allowed'] = 'n';
        $arr_user_cred['start_date']     = date("Y-m-d H:i:s");
        $arr_user_cred['end_date']       = isset($arr_query_data['end_date']) ? $arr_query_data['end_date'] : null;
        $arr_user_cred['first_name']     = isset($arr_form_data['first_name']) ? $arr_form_data['first_name'] : null;
        $arr_user_cred['last_name']      = isset($arr_form_data['last_name']) ? $arr_form_data['last_name'] : null;
        $arr_user_cred['middle_name']    = isset($arr_form_data['middle_name']) ? $arr_form_data['middle_name'] : null;
        $arr_user_cred['email']          = isset($arr_form_data['email']) ? $arr_form_data['email'] : null;
        $arr_user_cred['phone']          = isset($arr_form_data['mobile_no']) ? $arr_form_data['mobile_no'] : null;
        $arr_user_cred['reset_login_flag'] = 'N';
        $arr_user_cred['locked']           = 0;
        $arr_user_cred['incorrect_login_attempt'] = 0;
        $arr_user_cred['attr1'] = isset($arr_form_data['attr1']) ? $arr_form_data['attr1'] : null;
        $arr_user_cred['attr2'] = isset($arr_form_data['attr2']) ? $arr_form_data['attr2'] : null;
        $arr_user_cred['avatar'] = !empty($join['profile_image_url'])
            ? $join['profile_image_url']
            : 'img/placeholdermen.jpeg';
        $arr_user_cred['user_group'] = 1;
        $arr_user_cred['reset_token'] = null;
        $arr_user_cred['token_expired_at'] = null;

        // debug($arr_user_cred);
        // Make sure model is loaded
        $this->loadModel('UserCredentials');

        $this->UserCredentials->create();
        $this->UserCredentials->save($arr_user_cred);
        $this->EmployeeProfessionalDetails->create();

        try {
            if ($this->EmployeeProfessionalDetails->save($arr_professional_data)) {

                // 🔹 Call procedure 'Linkemp_deviceanddatabase'
                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                $outputParameter = [];
                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; // company_code
                $outputParameter[] = isset($arr_query_data['emp_branch']) ? "'" . $arr_query_data['emp_branch'] . "'" : "''";
                $outputParameter[] = "''";

                try {
                    $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);

                    // 🔹 Check for duplicate ID error returned from procedure
                    if (!empty($out[0][0]['err_msg'])) {
                        $this->restsave($emp_pkey, $empJoinPkey);
                        return json_encode([
                            'success' => false,
                            'message' => $out[0][0]['err_msg'],
                            'pkey'    => $emp_pkey
                        ]);
                    }

                    // 🔹 Update UserCredentials with profile data (other data)
                    

                    // 🔹 Special logic for VSFS
                    $company = strtoupper($this->Session->read('company_code'));
                    if ($company == 'VSFS') {
                        $empdeviceid = $this->UserCredentials->query("select SUBSTRING(max(trim(emp_company_id)),3, 10) as deviceid from emp_proff 
                            where emp_company_id like '%VS%' AND emp_company_id NOT REGEXP 'VSFS' and emp_fkey!=$emp_pkey");
                        $empdeviceid1 = $empdeviceid['0']['0']['deviceid'];
                        $empdeviceid1++;
                        $this->EmployeeProfessionalDetails->query("update emp_proff set emp_company_id = concat('VS',$empdeviceid1) where emp_fkey=$emp_pkey");
                    }

                    // 🔹 Save Nominee (Family) if present
                    if (isset($arr_form_data['nominee'])) {
                        $this->Family->useDbConfig = $this->Session->read('ds');
                        $arr_form_datas = [];
                        $arr_form_datas['emp_family_pkey'] = isset($arr_form_data['emp_family_pkey']) ? $arr_form_data['emp_family_pkey'] : '';
                        $arr_form_datas['emp_fkey']        = $emp_pkey;
                        $arr_form_datas['name']            = isset($arr_form_data['nominee']) ? $arr_form_data['nominee'] : '';
                        $arr_form_datas['relation']        = isset($arr_form_data['relation']) ? $arr_form_data['relation'] : '';
                        $arr_form_datas['DOB']             = isset($arr_form_data['date_of_birth_nominee']) ? $arr_form_data['date_of_birth_nominee'] : '';
                        $arr_form_datas['is_nominee']      = "Y";
                        $arr_form_datas['created_by']      = $this->Session->read('user_name');
                        $this->Family->save($arr_form_datas);
                    }

                } catch (Exception $ex) {
                    $this->restsave($emp_pkey, $empJoinPkey);
                    $message = 'Link Employee Details Saving Failed.Please Save again.';
                    return json_encode(['success' => false, 'error' => $ex->getMessage(), 'pkey' => $emp_pkey, 'message' => $message]);
                }
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Failed to save professional details. Please try again.'
                ]);
            }
        } catch (Exception $ex) {
            $this->restsave($emp_pkey, $empJoinPkey);
            $message = 'Proffessional Details Saving Failed';
            if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
            return json_encode(['success' => false, 'error' => $ex->getMessage(), 'pkey' => $emp_pkey, 'message' => $message]);
        }



        $this->loadModel('EmployeeJoin');
        $this->EmployeeJoin->updateAll(
            ['EmployeeJoin.status' => 0],
            ['EmployeeJoin.emp_join_pkey' => $empJoinPkey]
        );

        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $arr_emp_config = [
            [
                'type'              => 'GRADE',
                'company_code'      => null,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => !empty($arr_query_data['emp_grade']) ? (int)$arr_query_data['emp_grade'] : null,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => !empty($authuser['username']) ? $authuser['username'] : null,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ],
            [
                'type'              => 'NOTICEPER',
                'company_code'      => null,
                'branch_code'       => null,
                'emp_fkey'          => $emp_pkey,
                'month_year'        => !empty($arr_query_data['month_year']) ? $arr_query_data['month_year'] : null,
                'policy_id'         => !empty($arr_query_data['notice_days']) ? (int)$arr_query_data['notice_days'] : null,
                'creation_date'     => date("Y-m-d H:i:s"),
                'created_by'        => !empty($authuser['username']) ? $authuser['username'] : null,
                'modified_by'       => null,
                'modification_date' => null,
                'hirc_leval'        => null,
                'hirc_up_flag'      => "N",
                'status'            => 1
            ]
        ];
        // debug($arr_query_data);
        // debug($arr_emp_config);

        // Save multiple rows
        $this->EmployeeConfig->create();
        $this->EmployeeConfig->saveMany($arr_emp_config);


        // ---------------- Education → Qualifications Mapping ----------------
        $this->Education->useDbConfig = $this->Session->read('ds');
        $this->qualifcations->useDbConfig = $this->Session->read('ds');

        // Get education data for this emp_join
        $educationData = $this->Education->find('all', [
            'conditions' => [
                'Education.emp_join_fkey' => $empJoinPkey,
                'Education.status' => 1
            ]
        ]);
        // debug($educationData);
        $joining_date = isset($arr_query_data['joining_date'])
            ? $arr_query_data['joining_date']
            : null;

        $end_date = isset($arr_query_data['end_date'])
            ? $arr_query_data['end_date']
            : null;

        $this->EmployeeProfessionalDetails->query("
    INSERT INTO contracted_days
    (
        emp_fkey,
        contract_start_date,
        contract_end_date,
        start_date_effective,
        status,
        created_by,
        created_time
    )
    VALUES
    (
        $emp_pkey,
        '$joining_date',
        '$end_date',
        NOW(),
        1,
        'null',
        NOW()
    )
");

        if (!empty($educationData)) {
            foreach ($educationData as $row) {
                $edu = $row['Education'];

                $qualification = [
                    'qualifcations' => [
                        'emp_fkey'   => $emp_pkey,
                        'course'     => $edu['course'],
                        'university' => $edu['university'],
                        'duration'   => $edu['duration'],
                        'mark'       => $edu['mark'],
                        'status'     => 1
                    ]
                ];
                // debug($qualification);
                // exit;
                $this->qualifcations->create();
                $this->qualifcations->save($qualification);
            }
        }
        //         if (!) {
        //             debug($this->qualifcations->validationErrors);
        //         } else {
        //             echo "Saved successfully!";
        //         }
        //     }
        // } else {
        //     echo "No education data to map.";
        // }

        // ---------------- WorkExperience → history Mapping ----------------
        $this->WorkExperience->useDbConfig = $this->Session->read('ds');
        $this->history->useDbConfig = $this->Session->read('ds');


        $workData = $this->WorkExperience->find('all', [
            'conditions' => [
                'WorkExperience.emp_join_fkey' => $empJoinPkey,
                'WorkExperience.status' => 1
            ]
        ]);

        if (!empty($workData)) {
            foreach ($workData as $row) {
                $exp = $row['WorkExperience'];

                $historyRecord = [
                    'history' => [
                        'emp_fkey'    => $emp_pkey,
                        'company'     => $exp['company'],
                        'from_date'   => $exp['from_date'],
                        'to_date'     => $exp['to_date'],
                        'designation' => $exp['designation'],
                        'department'  => $exp['department'],
                        'salary' => isset($exp['salary']) ? $exp['salary'] : null,
                        'status'      => 1
                    ]
                ];

                $this->history->create();
                $this->history->save($historyRecord);
            }
        }
        //         if (!$this->history->save($historyRecord)) {
        //             debug($this->history->validationErrors);
        //         } else {
        //             echo "Work experience mapped to history successfully!<br>";
        //         }
        //     }
        // } else {
        //     echo "No experience data to map.";
        // }



        // ---------------- Family → empfamily ----------------

        // 🔹 Set DB connections
        $this->EmpFam->useDbConfig = $this->Session->read('ds'); // source table (family)
        $this->Family->useDbConfig = $this->Session->read('ds'); // target table (emp_family)

        // Fetch data from source table (misconfigured EmpFam points to 'family')
        $familyData = $this->EmpFam->find('all', [
            'conditions' => [
                'EmpFam.emp_join_fkey' => $empJoinPkey,  // source column exists in 'family'
                'EmpFam.status' => '1'
            ]
        ]);
        // debug($familyData);
        if (!empty($familyData)) {
            foreach ($familyData as $row) {
                $fam = $row['EmpFam'];

                // Skip empty records
                if (empty($fam['name'])) continue;

                // Save into target table (misconfigured Family points to 'emp_family')
                $this->Family->create();
                $this->Family->save([
                    'Family' => [
                        'emp_fkey'       => $emp_pkey, // permanent employee key
                        'name'           => $fam['name'],
                        'DOB'            => $fam['DOB'],
                        'gender'         => $fam['gender'],
                        'relation'       => $fam['relation'],
                        'blood_group'    => $fam['blood_group'],
                        'nationality'    => $fam['nationality'],
                        'contact_number' => $fam['contact_number'],
                        'alternate_number' => $fam['alternate_number'],
                        'emergency_contact' => $fam['emergency_contact'],
                        'remarks'        => $fam['remarks'],
                        'created_date'   => $fam['created_date'],
                        'created_by'     => $fam['created_by'],
                        'modified_by'    => $fam['modified_by'],
                        'modified_date'  => $fam['modified_date'],
                        'is_nominee'     => $fam['is_nominee'],
                        'status'         => 1
                    ]
                ]);
            }
        }
        // } else {
        //     echo "No family data to map.";
        // }

        // 🔹 Source: emp_documents → EmpDocument model
        $this->EmpDocument->useDbConfig = $this->Session->read('ds');

        // 🔹 Target: emp_passport_visa → passport model
        $this->passport->useDbConfig = $this->Session->read('ds');

        // Fetch all documents for this employee
        $empDocs = $this->EmpDocument->find('all', [
            'conditions' => [
                'EmpDocument.emp_join_fkey' => $empJoinPkey,
                'EmpDocument.status'   => 1
            ]
        ]);

        if (!empty($empDocs)) {
            foreach ($empDocs as $row) {
                $doc = $row['EmpDocument'];

                // Skip if essential fields empty
                if (empty($doc['document_type']) || empty($doc['document_number'])) continue;

                $passportRecord = [
                    'passport' => [
                        'emp_fkey'  => $emp_pkey,  // reverse mapping
                        'document_type'  => $doc['document_type'],
                        'document_number' => $doc['document_number'],
                        'classification' => $doc['classification'],
                        'name'           => $doc['name'],
                        'relation'       => $doc['relation'],
                        'valid_from'     => $doc['valid_from'],
                        'valid_till'     => $doc['valid_till'],
                        'nationality'    => $doc['nationality'],
                        'remarks'        => $doc['remarks'],
                        'files'          => $doc['files'],
                        'created_date'   => $doc['created_date'],
                        'created_by'     => $doc['created_by'],
                        'modified_by'    => $doc['modified_by'],
                        'modified_date'  => $doc['modified_date'],
                        'reccuring'      => $doc['reccuring'],
                        'remind'         => $doc['remind'],
                        'status'         => 1
                    ]
                ];

                $this->passport->create();
                $this->passport->save($passportRecord);
                // if (!$this->passport->save($passportRecord)) {
                //     debug($this->passport->validationErrors);
                // }
            }
        }


        // 🔹 Step 8: Final response
        $result = [
            'success'          => true,
            'message'          => 'Employee company details saved successfully',
            'emp_join_pkey'    => $empJoinPkey,
            'emp_pkey'         => $emp_pkey,
            'data'             => $arr_form_data,
            'emp_proff'        => $arr_professional_data,
            'user_credentials' => $arr_user_cred
        ];

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function restsave($emp_pkey = 0, $empJoinPkey = 0)
    {
        $this->loadModel('UserCredentials');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        try {
            $this->EmployeeProfessionalDetails->query("delete from emp_details where emp_pkey = $emp_pkey ");
            $this->EmployeeProfessionalDetails->query("delete from emp_proff where emp_fkey = $emp_pkey ");
            $this->EmployeeProfessionalDetails->query("delete from user_credentials where emp_fkey = $emp_pkey ");
            // $this->EmployeeProfessionalDetails->query("delete from mypayrol_control_db.emp_device_comp_branch where emp_fkey = $emp_pkey and Company_code = '$company_code'");
            
            if ($empJoinPkey > 0) {
                $this->EmployeeProfessionalDetails->query("update emp_join set emp_fkey = 0 where emp_join_pkey = $empJoinPkey");
            }

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    // public function uploadandsaveempdetail()
    // {
    //     $this->autoRender = false;
    //     $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

    //     $authuser['company_code'] = $this->Session->read('company_code');
    //     $filename = $authuser['company_code'] . '_' . strtotime("now") . '.xlsx';
    //     $targetpath = getcwd() . "/files/" . $filename;

    //     if (move_uploaded_file($_FILES['empdatacsv']['tmp_name'], $targetpath)) {

    //         App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
    //         $objReader = new PHPExcel_Reader_Excel2007();
    //         $objPHPExcel = $objReader->load($targetpath);

    //         $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    //         $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

    //         $imported = 0;
    //         $errors = [];
    //         $duplicates = [];

    //         // Read header
    //         $header = [];
    //         foreach (range('A', $highestColumn) as $col) {
    //             $header[] = trim($objPHPExcel->getActiveSheet()->getCell($col . '1')->getValue());
    //         }

    //         for ($row = 2; $row <= $highestRow; $row++) {
    //             $rowData = [];
    //             foreach ($header as $i => $colName) {
    //                 $colLetter = chr(65 + $i);
    //                 $rowData[$colName] = trim($objPHPExcel->getActiveSheet()->getCell($colLetter . $row)->getValue());
    //             }

    //             $saveData = [
    //                 'EmployeeJoin' => [
    //                     'emp_name'      => isset($rowData['First Name']) ? $rowData['First Name'] : null,
    //                     'last_name'     => isset($rowData['Last Name']) ? $rowData['Last Name'] : null,
    //                     'gender'        => isset($rowData['Gender(Male/Female)']) ? strtolower($rowData['Gender(Male/Female)']) : null,
    //                     'date_of_birth' => !empty($rowData['Date of Birth (DD-MM-YYYY)']) ? date("Y-m-d", strtotime($rowData['Date of Birth (DD-MM-YYYY)'])) : null,
    //                     'id_card'       => isset($rowData['ID/AADHAR Card Number']) ? $rowData['ID/AADHAR Card Number'] : null,
    //                     'joining_date'  => !empty($rowData['Joining Date (DD-MM-YYYY)']) ? date("Y-m-d", strtotime($rowData['Joining Date (DD-MM-YYYY)'])) : null,
    //                     'designation'   => isset($rowData['Designation CODE']) ? $rowData['Designation CODE'] : null,
    //                     'department'    => isset($rowData['Department CODE']) ? $rowData['Department CODE'] : null,
    //                     'company_code'  => $authuser['company_code'],
    //                     'status'        => 1
    //                 ]
    //             ];

    //             $exists = $this->EmployeeJoin->find('first', [
    //                 'conditions' => [
    //                     'EmployeeJoin.id_card' => $saveData['EmployeeJoin']['id_card'],
    //                     'EmployeeJoin.status' => 1
    //                 ]
    //             ]);

    //             if ($exists) {
    //                 $duplicates[] = $saveData['EmployeeJoin']['id_card'];
    //                 continue;
    //             }

    //             $this->EmployeeJoin->create();
    //             if ($this->EmployeeJoin->save($saveData)) {
    //                 $imported++;
    //             } else {
    //                 $errors[] = $saveData['EmployeeJoin']['emp_name'];
    //             }
    //         }

    //         unlink($targetpath);
    //         echo json_encode([
    //             'success' => 1,
    //             'msg' => 'Import completed',
    //             'imported_count' => $imported,
    //             'duplicate_list' => $duplicates,
    //             'errors' => $errors
    //         ]);
    //         exit;
    //     } else {
    //         echo json_encode(['success' => 0, 'msg' => 'File upload failed']);
    //         exit;
    //     }
    // }

    // public function uploadandsaveempdetail()
    // {
    //     $this->autoRender = false;
    //     $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');

    //     $authuser['company_code'] = $this->Session->read('company_code');
    //     $filename = $authuser['company_code'] . '_' . time() . '.xlsx';
    //     $targetpath = getcwd() . "/files/" . $filename;

    //     // Check if file uploaded
    //     if (!empty($_FILES['empdatacsv']['tmp_name'])) {

    //         // Move uploaded file
    //         if (!move_uploaded_file($_FILES['empdatacsv']['tmp_name'], $targetpath)) {
    //             echo json_encode(['success' => 0, 'msg' => 'File upload failed']);
    //             exit;
    //         }

    //         // Load PHPExcel
    //         App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
    //         $objReader = new PHPExcel_Reader_Excel2007();
    //         $objPHPExcel = $objReader->load($targetpath);
    //         $sheet = $objPHPExcel->getActiveSheet();

    //         $highestRow = $sheet->getHighestRow();
    //         $highestColumn = $sheet->getHighestColumn();
    //         $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    //         // Read header row
    //         $header = [];
    //         for ($col = 0; $col < $highestColumnIndex; $col++) {
    //             $header[] = trim($sheet->getCellByColumnAndRow($col, 1)->getValue());
    //         }

    //         $imported = 0;
    //         $errors = [];

    //         // Loop through rows
    //         for ($row = 2; $row <= $highestRow; $row++) {
    //             $rowData = [];
    //             for ($col = 0; $col < $highestColumnIndex; $col++) {
    //                 $rowData[$header[$col]] = trim($sheet->getCellByColumnAndRow($col, $row)->getValue());
    //             }

    //             // Skip empty rows
    //             if (empty($rowData['First Name'])) {
    //                 continue;
    //             }

    //             // Map to EmployeeJoin fields
    //             $saveData = [
    //                 'EmployeeJoin' => [
    //                     'first_name'       => isset($rowData['First Name']) && $rowData['First Name'] !== '' ? $rowData['First Name'] : null,
    //                     'last_name'        => isset($rowData['Last Name']) && $rowData['Last Name'] !== '' ? $rowData['Last Name'] : null,
    //                     'classification'   => isset($rowData['Gender(Male/Female)']) && $rowData['Gender(Male/Female)'] !== '' ? $rowData['Gender(Male/Female)'] : null,
    //                     'address'          => isset($rowData['Address']) && $rowData['Address'] !== '' ? $rowData['Address'] : null,
    //                     'district'         => isset($rowData['City']) && $rowData['City'] !== '' ? $rowData['City'] : null,
    //                     'state'            => isset($rowData['State']) && $rowData['State'] !== '' ? $rowData['State'] : null,
    //                     'pincode'          => isset($rowData['Zip']) && $rowData['Zip'] !== '' ? $rowData['Zip'] : null,
    //                     'mobile_no'        => isset($rowData['Mobile No']) && $rowData['Mobile No'] !== '' ? $rowData['Mobile No'] : null,
    //                     'email'            => isset($rowData['Email']) && $rowData['Email'] !== '' ? $rowData['Email'] : null,
    //                     'maritual_status'  => isset($rowData['Marital Status(Single/Married)']) && $rowData['Marital Status(Single/Married)'] !== '' ? $rowData['Marital Status(Single/Married)'] : null,
    //                     'date_of_birth'    => isset($rowData['Date of Birth (DD-MM-YYYY)']) && $rowData['Date of Birth (DD-MM-YYYY)'] !== '' ? date('Y-m-d', strtotime($rowData['Date of Birth (DD-MM-YYYY)'])) : null,
    //                     'bank'             => isset($rowData['Bank Name']) && $rowData['Bank Name'] !== '' ? $rowData['Bank Name'] : null,
    //                     'bank_branch'      => isset($rowData['Bank Branch Name']) && $rowData['Bank Branch Name'] !== '' ? $rowData['Bank Branch Name'] : null,
    //                     'ifsc_code'        => isset($rowData['Bank IFSC']) && $rowData['Bank IFSC'] !== '' ? $rowData['Bank IFSC'] : null,
    //                     'account_no'       => isset($rowData['Account No']) && $rowData['Account No'] !== '' ? $rowData['Account No'] : null,
    //                     'pf'               => isset($rowData['SPK No']) && $rowData['SPK No'] !== '' ? $rowData['SPK No'] : null,
    //                     'esi'              => isset($rowData['ESI NUMBER']) && $rowData['ESI NUMBER'] !== '' ? $rowData['ESI NUMBER'] : null,
    //                     'eps'              => isset($rowData['UAN Number']) && $rowData['UAN Number'] !== '' ? $rowData['UAN Number'] : null,
    //                     'pan_no'           => isset($rowData['PAN No']) && $rowData['PAN No'] !== '' ? $rowData['PAN No'] : null,
    //                     'status'           => 1,
    //                     'company_code'     => isset($authuser['company_code']) && $authuser['company_code'] !== '' ? $authuser['company_code'] : null
    //                 ]
    //             ];

    //             // Save record
    //             $this->EmployeeJoin->create();
    //             if ($this->EmployeeJoin->save($saveData)) {
    //                 $imported++;
    //             } else {
    //                 $errors[] = [
    //                     'emp_name' => $saveData['EmployeeJoin']['first_name'],
    //                     'validationErrors' => $this->EmployeeJoin->validationErrors
    //                 ];
    //             }
    //         }

    //         // Delete uploaded file
    //         unlink($targetpath);

    //         // Return JSON response
    //         echo json_encode([
    //             'success' => 1,
    //             'msg' => 'Import completed',
    //             'imported_count' => $imported,
    //             'errors' => $errors
    //         ]);
    //         exit;
    //     } else {
    //         echo json_encode(['success' => 0, 'msg' => 'No file uploaded']);
    //         exit;
    //     }
    // }


    // public function uploadandsaveempdetail()
    // {
    //     $this->autoRender = false;
    //     $ds = $this->Session->read('ds');
    //     $this->EmployeeJoin->useDbConfig = $ds;
    //     $this->EmployeeDetails->useDbConfig = $ds;

    //     $company_code = $this->Session->read('company_code');
    //     $filename = $company_code . '_' . time() . '.xlsx';
    //     $targetpath = getcwd() . "/files/" . $filename;

    //     if (empty($_FILES['empdatacsv']['tmp_name'])) {
    //         echo json_encode(['success' => 0, 'msg' => 'No file uploaded']);
    //         exit;
    //     }

    //     if (!move_uploaded_file($_FILES['empdatacsv']['tmp_name'], $targetpath)) {
    //         echo json_encode(['success' => 0, 'msg' => 'File upload failed']);
    //         exit;
    //     }

    //     App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
    //     $objReader = new PHPExcel_Reader_Excel2007();
    //     $objPHPExcel = $objReader->load($targetpath);
    //     $sheet = $objPHPExcel->getActiveSheet();

    //     $highestRow = $sheet->getHighestRow();
    //     $highestColumn = $sheet->getHighestColumn();
    //     $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    //     // Read headers
    //     $header = [];
    //     for ($col = 0; $col < $highestColumnIndex; $col++) {
    //         $header[] = trim($sheet->getCellByColumnAndRow($col, 1)->getValue());
    //     }

    //     $imported = 0;
    //     $errors = [];

    //     // Helper functions
    //     function titleCase($str)
    //     {
    //         return ucwords(strtolower(trim($str)));
    //     }

    //     function fixScientific($val)
    //     {
    //         if (is_numeric($val) && stripos($val, 'E') !== false) {
    //             return number_format($val, 0, '', '');
    //         }
    //         return trim($val);
    //     }

    //     function limitLength($val, $len)
    //     {
    //         return substr(trim($val), 0, $len);
    //     }

    //     function yesNoFlag($val)
    //     {
    //         $val = strtolower(trim($val));
    //         return ($val === 'yes' || $val === 'y' || $val === 'on') ? 'Y' : 'N';
    //     }

    //     function epsFlag($val)
    //     {
    //         $val = strtolower(trim($val));
    //         return ($val === 'yes' || $val === 'y') ? 'on' : null;
    //     }

    //     // --- Fetch existing Aadhaar / PAN once ---
    //     $existingJoin = $this->EmployeeJoin->find('all', ['fields' => ['id_card', 'pan_no']]);
    //     $existingDetails = $this->EmployeeDetails->find('all', ['fields' => ['id_card', 'pan_no']]);

    //     $existingIdCards = [];
    //     $existingPans = [];
    //     foreach (array_merge($existingJoin, $existingDetails) as $row) {
    //         $id_card_val = isset($row['EmployeeJoin']['id_card']) ? $row['EmployeeJoin']['id_card'] : (isset($row['EmployeeDetails']['id_card']) ? $row['EmployeeDetails']['id_card'] : null);

    //         $pan_val = isset($row['EmployeeJoin']['pan_no']) ? strtoupper($row['EmployeeJoin']['pan_no']) : (isset($row['EmployeeDetails']['pan_no']) ? strtoupper($row['EmployeeDetails']['pan_no']) : null);

    //         if ($id_card_val) $existingIdCards[] = $id_card_val;
    //         if ($pan_val) $existingPans[] = $pan_val;
    //     }
    //     for ($row = 2; $row <= $highestRow; $row++) {
    //         $rowData = [];
    //         for ($col = 0; $col < $highestColumnIndex; $col++) {
    //             $cell = $sheet->getCellByColumnAndRow($col, $row);
    //             $cell->setDataType(PHPExcel_Cell_DataType::TYPE_STRING);
    //             $rowData[$header[$col]] = trim($cell->getValue());
    //         }

    //         // Skip empty rows
    //         if (empty($rowData['Name *']) && empty($rowData['Aadhaar No *']) && empty($rowData['PAN No *'])) {
    //             continue;
    //         }

    //         // Fetch countries (for both Country of Origin and Nationality)
    //         // Fetch all countries
    //         $countries = $this->EmployeeDetails->query("SELECT id, country_name, country_code FROM countries ORDER BY id ASC");

    //         // Create a mapping: lowercase name/code => id
    //         $countryMap = [];
    //         foreach ($countries as $row) {
    //             $id = $row['countries']['id'];
    //             $name = strtolower(trim($row['countries']['country_name'])); // e.g., "india"
    //             $code = strtolower(trim($row['countries']['country_code'])); // e.g., "indian"

    //             // Map both name and code to the same ID
    //             $countryMap[$name] = $id;
    //             $countryMap[$code] = $id;
    //         }




    //         // Normalize case
    //         $name      = titleCase($rowData['Name *']);
    //         $gender    = strtolower(trim($rowData['Gender *']));
    //         $marital   = titleCase($rowData['Marital Status']);
    //         $guardian  = titleCase($rowData['Guardian Name']);
    //         $relation  = titleCase($rowData['Relation']);
    //         $blood     = strtoupper(trim($rowData['Blood Group']));
    //         $state     = titleCase($rowData['State']);
    //         $district  = titleCase($rowData['District']);
    //         $bank      = titleCase($rowData['Bank Name']);
    //         $branch    = titleCase($rowData['Branch']);
    //         $address   = titleCase($rowData['Address']);

    //         // Fix numeric values
    //         $aadhaar     = fixScientific($rowData['Aadhaar No *']);
    //         $uan         = fixScientific($rowData['UAN No']);
    //         $pf          = fixScientific($rowData['PF No']);
    //         $account     = fixScientific($rowData['Account Number']);
    //         $esi         = fixScientific($rowData['ESI No']);
    //         $wps         = fixScientific($rowData['WPS ID']);
    //         $lwf         = fixScientific($rowData['LWF Registration No']);
    //         $prev_member = fixScientific($rowData['Previous Member ID']);
    //         $ifsc        = strtoupper(trim($rowData['IFSC Code']));


    //         $eps_flag         = epsFlag($rowData['EPS Eligibility (Yes/No)']); // on/null
    //         $phy_flag         = yesNoFlag($rowData['Physical Handicap (Yes/No)']);
    //         $int_flag         = yesNoFlag($rowData['International Worker (Yes/No)']);
    //         $locomotive_flag  = yesNoFlag($rowData['Locomotive (Yes/No)']);
    //         $hearing_flag     = yesNoFlag($rowData['Hearing (Yes/No)']);
    //         $visual_flag      = yesNoFlag($rowData['Visual (Yes/No)']);

    //         // Normalize input (lowercase + trim)
    //         $countryOriginInput = strtolower(trim($rowData['Country of Origin']));
    //         $nationalityInput = strtolower(trim($rowData['Nationality *']));

    //         // Fetch ID if matches country name or code (case-insensitive)
    //         $country_origin = isset($countryMap[$countryOriginInput]) ? $countryMap[$countryOriginInput] : null;
    //         $nationality_id = isset($countryMap[$nationalityInput]) ? $countryMap[$nationalityInput] : null;



    //         $dobValue = $rowData['Birth Date * (yyyy-mm-dd)'];

    //         if (is_numeric($dobValue)) {

    //             $date_of_birth = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($dobValue));
    //         } else {

    //             $date_of_birth = !empty($dobValue) ? date('Y-m-d', strtotime($dobValue)) : null;
    //         }


    //         $id_card = limitLength($aadhaar, 12);
    //         $pan_no  = strtoupper(limitLength($rowData['PAN No *'], 10));

    //         // --- Check duplicates ---
    //         if (in_array($id_card, $existingIdCards) || in_array($pan_no, $existingPans)) {
    //             $errors[] = [
    //                 'row' => $row,
    //                 'error' => 'Duplicate ID Card or PAN No. Row skipped.'
    //             ];
    //             continue;
    //         }
    //         // Prepare data for saving
    //         $saveData = [
    //             'EmployeeJoin' => [
    //                 'first_name'          => limitLength($name, 50),
    //                 'date_of_birth' => $date_of_birth,
    //                 'classification'      => limitLength($gender, 10),
    //                 'email'               => strtolower(limitLength($rowData['Email Address'], 100)),
    //                 'mobile_no'           => limitLength($rowData['Phone Number'], 15),
    //                 'address'             => limitLength($address, 255),
    //                 'district'            => limitLength($district, 50),
    //                 'pincode'             => limitLength($rowData['Pin Code'], 10),
    //                 'blood'               => limitLength($blood, 5),
    //                 'maritual_status'     => limitLength($marital, 15),
    //                 'guradian'            => limitLength($guardian, 50),
    //                 'relation_guardian'   => limitLength($relation, 50),
    //                 // 'nationality'         => limitLength($rowData['Country of Origin'], 50),
    //                 'state'               => limitLength($state, 50),
    //                 'bank'                => limitLength($bank, 50),
    //                 'bank_branch'         => limitLength($branch, 50),
    //                 'ifsc_code'           => limitLength($ifsc, 20),
    //                 'account_no'          => limitLength($account, 25),
    //                 'pf'                  => limitLength($pf, 20),
    //                 'id_card'             => $id_card,
    //                 'company_pf'          => limitLength($uan, 20),
    //                 'previous_member_id'  => limitLength($prev_member, 20),
    //                 'esi'                 => limitLength($esi, 15),
    //                 'esi_dispensary'      => limitLength($rowData['ESI Dispensary'], 50),
    //                 'wps_code'            => limitLength($wps, 20),
    //                 'lwf_code'            => limitLength($lwf, 20),
    //                 'pan_no'              => $pan_no,
    //                 'eps'                 => $eps_flag,
    //                 'status'              => 1,
    //                 'physical_handicap'   => $phy_flag,
    //                 'international_worker' => $int_flag,
    //                 'locomotive'          => $locomotive_flag,
    //                 'hearing'             => $hearing_flag,
    //                 'visual'              => $visual_flag,
    //                 'nationality_id' => $nationality_id,  // mapped ID from countries table
    //                 'country_origin' => $country_origin,  // mapped ID from countries table
    //                 // 'company_code'        => $company_code
    //             ]
    //         ];
    //         // debug($saveData);

    //         $this->EmployeeJoin->create();
    //         if ($this->EmployeeJoin->save($saveData)) {
    //             $imported++;
    //             $existingIdCards[] = $id_card;
    //             $existingPans[] = $pan_no;
    //         } else {
    //             $errors[] = ['row' => $row, 'error' => 'Database save failed'];
    //         }
    //     }

    //     unlink($targetpath);

    //     if (!empty($errors)) {
    //         echo json_encode([
    //             'success' => 0,
    //             'msg' => 'Import completed with some errors',
    //             'imported_count' => $imported,
    //             'errors' => $errors
    //         ]);
    //     } else {
    //         echo json_encode([
    //             'success' => 1,
    //             'msg' => 'All records imported successfully',
    //             'imported_count' => $imported
    //         ]);
    //     }
    //     exit;
    // }

    // public function uploadandsaveempdetail()
    // {
    //     $this->autoRender = false;
    //     $ds = $this->Session->read('ds');
    //     $this->EmployeeJoin->useDbConfig = $ds;
    //     $this->EmployeeDetails->useDbConfig = $ds;

    //     $company_code = $this->Session->read('company_code');
    //     $filename = $company_code . '_' . time() . '.xlsx';
    //     $targetpath = getcwd() . "/files/" . $filename;

    //     if (empty($_FILES['empdatacsv']['tmp_name'])) {
    //         echo json_encode(['success' => 0, 'msg' => 'No file uploaded']);
    //         exit;
    //     }

    //     if (!move_uploaded_file($_FILES['empdatacsv']['tmp_name'], $targetpath)) {
    //         echo json_encode(['success' => 0, 'msg' => 'File upload failed']);
    //         exit;
    //     }

    //     App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
    //     $objReader = new PHPExcel_Reader_Excel2007();
    //     $objPHPExcel = $objReader->load($targetpath);
    //     $sheet = $objPHPExcel->getActiveSheet();

    //     $highestRow = $sheet->getHighestRow();
    //     $highestColumn = $sheet->getHighestColumn();
    //     $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    //     // Read headers
    //     $header = [];
    //     for ($col = 0; $col < $highestColumnIndex; $col++) {
    //         $header[] = trim($sheet->getCellByColumnAndRow($col, 1)->getValue());
    //     }

    //     $imported = 0;
    //     $errors = [];
    //     $duplicateCount = 0;

    //     // Mandatory fields
    //     $mandatoryFields = ['Name *', 'Birth Date * (yyyy-mm-dd)', 'Gender *', 'Nationality *', 'Aadhaar No *', 'PAN No *'];

    //     // Helper functions
    //     function titleCase($str)
    //     {
    //         return ucwords(strtolower(trim($str)));
    //     }
    //     function fixScientific($val)
    //     {
    //         if (is_numeric($val) && stripos($val, 'E') !== false) {
    //             return number_format($val, 0, '', '');
    //         }
    //         return trim($val);
    //     }
    //     function limitLength($val, $len)
    //     {
    //         return substr(trim($val), 0, $len);
    //     }
    //     function yesNoFlag($val)
    //     {
    //         $val = strtolower(trim($val));
    //         return ($val === 'yes' || $val === 'y' || $val === 'on') ? 'Y' : 'N';
    //     }
    //     function epsFlag($val)
    //     {
    //         $val = strtolower(trim($val));
    //         return ($val === 'yes' || $val === 'y') ? 'on' : null;
    //     }

    //     // --- Fetch existing Aadhaar / PAN once ---
    //     $existingJoin = $this->EmployeeJoin->find('all', ['fields' => ['id_card', 'pan_no']]);
    //     $existingDetails = $this->EmployeeDetails->find('all', ['fields' => ['id_card', 'pan_no']]);

    //     $existingIdCards = [];
    //     $existingPans = [];
    //     foreach (array_merge($existingJoin, $existingDetails) as $row) {
    //         $id_card_val = isset($row['EmployeeJoin']['id_card']) ? $row['EmployeeJoin']['id_card'] : (isset($row['EmployeeDetails']['id_card']) ? $row['EmployeeDetails']['id_card'] : null);
    //         $pan_val = isset($row['EmployeeJoin']['pan_no']) ? strtoupper($row['EmployeeJoin']['pan_no']) : (isset($row['EmployeeDetails']['pan_no']) ? strtoupper($row['EmployeeDetails']['pan_no']) : null);
    //         if ($id_card_val) $existingIdCards[] = $id_card_val;
    //         if ($pan_val) $existingPans[] = $pan_val;
    //     }

    //     for ($row = 2; $row <= $highestRow; $row++) {
    //         $rowData = [];
    //         for ($col = 0; $col < $highestColumnIndex; $col++) {
    //             $cell = $sheet->getCellByColumnAndRow($col, $row);
    //             $cell->setDataType(PHPExcel_Cell_DataType::TYPE_STRING);
    //             $rowData[$header[$col]] = trim($cell->getValue());
    //         }

    //         // Skip empty rows
    //         if (empty($rowData['Name *']) && empty($rowData['Aadhaar No *']) && empty($rowData['PAN No *'])) {
    //             continue;
    //         }

    //         // --- Check mandatory fields ---
    //         $missingFields = [];
    //         foreach ($mandatoryFields as $field) {
    //             if (empty($rowData[$field])) {
    //                 $missingFields[] = $field;
    //             }
    //         }

    //         if (!empty($missingFields)) {
    //             $errors[] = [
    //                 'row' => $row,
    //                 'error' => 'Mandatory fields missing: ' . implode(', ', $missingFields)
    //             ];
    //             continue; // Skip this row
    //         }
    //         $birthDate = trim($rowData['Birth Date * (yyyy-mm-dd)']);

    //         if (!empty($birthDate)) {

    //             // If Excel serial number (e.g., 45658)
    //             if (is_numeric($birthDate) && $birthDate > 30000) {
    //                 // Convert Excel serial to PHP date
    //                 $unix_date = ($birthDate - 25569) * 86400;
    //                 $birth = (new DateTime())->setTimestamp($unix_date);
    //             } else {
    //                 // Supported formats
    //                 $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];

    //                 $birth = false;
    //                 foreach ($formats as $format) {
    //                     $tmp = DateTime::createFromFormat($format, $birthDate);
    //                     if ($tmp && $tmp->format($format) === $birthDate) {
    //                         $birth = $tmp;
    //                         break;
    //                     }
    //                 }
    //             }

    //             // Invalid date
    //             if (!$birth) {
    //                 $errors[] = [
    //                     'row' => $row,
    //                     'error' => "Invalid Birth Date format or Excel serial date"
    //                 ];
    //                 continue;
    //             }

    //             // Calculate age
    //             $today = new DateTime();
    //             $age = $birth->diff($today)->y;


    //             if ($age < 18) {
    //                 $errors[] = [
    //                     'row' => $row,
    //                     'error' => "Age must be above 18 (current age: $age)."
    //                 ];
    //                 continue;
    //             }
    //         }

    //         // --- Check for invalid special characters ---
    //         $specialCharPattern = '/[^a-zA-Z0-9\s\-\/,()&]/'; // Allowed: letters, numbers, space, -, /, , ( ), &
    //         $invalidFields = [];

    //         foreach ($rowData as $field => $value) {
    //             if (empty($value)) continue;

    //             // Skip email field check (allow @ and .)
    //             if (stripos($field, 'Email') !== false) {
    //                 if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
    //                     $invalidFields[] = "$field (invalid email format)";
    //                 }
    //                 continue;
    //             }

    //             // Check if contains special characters
    //             if (preg_match($specialCharPattern, $value)) {
    //                 $invalidFields[] = $field;
    //             }
    //         }

    //         if (!empty($invalidFields)) {
    //             $errors[] = [
    //                 'row' => $row,
    //                 'error' => 'Special characters not allowed in: ' . implode(', ', $invalidFields)
    //             ];
    //             continue; // Skip this row
    //         }


    //         // Fetch all countries for mapping
    //         $countries = $this->EmployeeDetails->query("SELECT id, country_name, nationality  FROM countries_nationality ORDER BY id ASC");
    //         $countryMap = [];
    //         foreach ($countries as $r) {
    //             $id = $r['countries_nationality']['id'];
    //             $name = strtolower(trim($r['countries_nationality']['country_name']));
    //             $code = strtolower(trim($r['countries_nationality']['nationality']));
    //             $countryMap[$name] = $id;
    //             $countryMap[$code] = $id;
    //         }

    //         // Normalize values
    //         $name = titleCase($rowData['Name *']);
    //         $gender = strtolower(trim($rowData['Gender *']));
    //         $marital = titleCase($rowData['Marital Status']);
    //         $guardian = titleCase($rowData['Guardian Name']);
    //         $relation = titleCase($rowData['Relation']);
    //         $blood = strtoupper(trim($rowData['Blood Group']));
    //         $state = titleCase($rowData['State']);
    //         $district = titleCase($rowData['District']);
    //         $bank = titleCase($rowData['Bank Name']);
    //         $branch = titleCase($rowData['Branch']);
    //         $address = titleCase($rowData['Address']);

    //         // Numeric fixes
    //         $aadhaar = fixScientific($rowData['Aadhaar No *']);
    //         $uan = fixScientific($rowData['UAN No']);
    //         $pf = fixScientific($rowData['PF No']);
    //         $account = fixScientific($rowData['Account Number']);
    //         $esi = fixScientific($rowData['ESI No']);
    //         $wps = fixScientific($rowData['WPS ID']);
    //         $lwf = fixScientific($rowData['LWF Registration No']);
    //         $prev_member = fixScientific($rowData['Previous Member ID']);
    //         $ifsc = strtoupper(trim($rowData['IFSC Code']));

    //         $eps_flag = epsFlag($rowData['EPS Eligibility (Yes/No)']);
    //         // $phy_flag = yesNoFlag($rowData['Physical Handicap (Yes/No)']);
    //         $int_flag = yesNoFlag($rowData['International Worker (Yes/No)']);
    //         $locomotive_flag = yesNoFlag($rowData['Locomotive (Yes/No)']);
    //         $hearing_flag = yesNoFlag($rowData['Hearing (Yes/No)']);
    //         $visual_flag = yesNoFlag($rowData['Visual (Yes/No)']);
    //         if ($locomotive_flag === 'Yes' || $hearing_flag === 'Yes' || $visual_flag === 'Yes') {
    //             $phy_flag = 'Yes';
    //         } else {
    //             $phy_flag = yesNoFlag($rowData['Physical Handicap (Yes/No)']);
    //         }

    //         // Country mapping
    //         $countryOriginInput = strtolower(trim($rowData['Country of Origin']));
    //         $nationalityInput = strtolower(trim($rowData['Nationality *']));
    //         $country_origin = isset($countryMap[$countryOriginInput]) ? $countryMap[$countryOriginInput] : null;
    //         $nationality_id = isset($countryMap[$nationalityInput]) ? $countryMap[$nationalityInput] : null;

    //         // Date conversion
    //         $dobValue = $rowData['Birth Date * (yyyy-mm-dd)'];
    //         if (is_numeric($dobValue)) {
    //             $date_of_birth = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($dobValue));
    //         } else {
    //             $date_of_birth = !empty($dobValue) ? date('Y-m-d', strtotime($dobValue)) : null;
    //         }

    //         $id_card = limitLength($aadhaar, 12);
    //         $pan_no = strtoupper(limitLength($rowData['PAN No *'], 10));

    //         // // --- Check duplicates ---
    //         // if (in_array($id_card, $existingIdCards) || in_array($pan_no, $existingPans)) {
    //         //     $errors[] = ['row' => $row, 'error' => 'Duplicate ID Card or PAN No. Row skipped.'];
    //         //     $duplicateCount++;
    //         //     continue;
    //         // }

    //         // --- Check duplicates separately ---
    //         $duplicateMsg = [];
    //         if (in_array($id_card, $existingIdCards)) {
    //             $duplicateMsg[] = "Duplicate Aadhaar No.";
    //         }
    //         if (in_array($pan_no, $existingPans)) {
    //             $duplicateMsg[] = "Duplicate PAN No.";
    //         }

    //         if (!empty($duplicateMsg)) {
    //             $errors[] = [
    //                 'row' => $row,
    //                 'error' => implode(' and ', $duplicateMsg) . ". Row skipped."
    //             ];

    //             // Count duplicates for each type separately if needed
    //             if (in_array($id_card, $existingIdCards)) $duplicateCount++;
    //             if (in_array($pan_no, $existingPans)) $duplicateCount++;

    //             continue;
    //         }


    //         // Prepare data
    //         $saveData = [
    //             'EmployeeJoin' => [
    //                 'first_name' => limitLength($name, 50),
    //                 'date_of_birth' => $date_of_birth,
    //                 'classification' => limitLength($gender, 10),
    //                 'email' => strtolower(limitLength($rowData['Email Address'], 100)),
    //                 'mobile_no' => limitLength($rowData['Phone Number'], 15),
    //                 'address' => limitLength($address, 255),
    //                 'district' => limitLength($district, 50),
    //                 'pincode' => limitLength($rowData['Pin Code'], 10),
    //                 'blood' => limitLength($blood, 5),
    //                 'maritual_status' => limitLength($marital, 15),
    //                 'guradian' => limitLength($guardian, 50),
    //                 'relation_guardian' => limitLength($relation, 50),
    //                 'state' => limitLength($state, 50),
    //                 'bank' => limitLength($bank, 50),
    //                 'bank_branch' => limitLength($branch, 50),
    //                 'ifsc_code' => limitLength($ifsc, 20),
    //                 'account_no' => limitLength($account, 25),
    //                 'pf' => limitLength($pf, 20),
    //                 'id_card' => $id_card,
    //                 'company_pf' => limitLength($uan, 20),
    //                 'previous_member_id' => limitLength($prev_member, 20),
    //                 'esi' => limitLength($esi, 15),
    //                 'esi_dispensary' => limitLength($rowData['ESI Dispensary'], 50),
    //                 'wps_code' => limitLength($wps, 20),
    //                 'lwf_code' => limitLength($lwf, 20),
    //                 'pan_no' => $pan_no,
    //                 'eps' => $eps_flag,
    //                 'status' => 1,
    //                 'physical_handicap' => $phy_flag,
    //                 'international_worker' => $int_flag,
    //                 'locomotive' => $locomotive_flag,
    //                 'hearing' => $hearing_flag,
    //                 'visual' => $visual_flag,
    //                 'nationality_id' => $nationality_id,
    //                 'country_origin' => $country_origin,
    //             ]
    //         ];

    //         $this->EmployeeJoin->create();
    //         if ($this->EmployeeJoin->save($saveData)) {
    //             $imported++;
    //             $existingIdCards[] = $id_card;
    //             $existingPans[] = $pan_no;
    //         } else {
    //             $errors[] = ['row' => $row, 'error' => 'Database save failed'];
    //         }
    //     }

    //     unlink($targetpath);

    //     // ---- Dynamic Message ----
    //     $messageParts = [];
    //     if ($imported > 0) $messageParts[] = "$imported record" . ($imported > 1 ? 's' : '') . " imported successfully";
    //     if ($duplicateCount > 0) $messageParts[] = "$duplicateCount duplicate" . ($duplicateCount > 1 ? 's were' : ' was') . " skipped";
    //     if (!empty($errors)) $messageParts[] = count($errors) . " error" . (count($errors) > 1 ? 's' : '') . " occurred";

    //     $msg = !empty($messageParts) ? implode(', ', $messageParts) . '.' : 'No records processed.';

    //     $response = [
    //         'success' => empty($errors) ? 1 : 0,
    //         'msg' => $msg,
    //         'imported_count' => $imported,
    //         'duplicate_count' => $duplicateCount,
    //         'error_count' => count($errors),
    //     ];

    //     if (!empty($errors)) $response['errors'] = $errors;

    //     echo json_encode($response);
    //     exit;
    // }
     public function uploadandsaveempdetail()
    {
        $this->autoRender = false;
        $ds = $this->Session->read('ds');
        $this->EmployeeJoin->useDbConfig = $ds;
        $this->EmployeeDetails->useDbConfig = $ds;

        $company_code = $this->Session->read('company_code');
        $filename = $company_code . '_' . time() . '.xlsx';
        $targetpath = getcwd() . "/files/" . $filename;

        if (empty($_FILES['empdatacsv']['tmp_name'])) {
            echo json_encode(['success' => 0, 'msg' => 'No file uploaded']);
            exit;
        }

        if (!move_uploaded_file($_FILES['empdatacsv']['tmp_name'], $targetpath)) {
            echo json_encode(['success' => 0, 'msg' => 'File upload failed']);
            exit;
        }

        App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
        $objReader = new PHPExcel_Reader_Excel2007();
        $objPHPExcel = $objReader->load($targetpath);
        $sheet = $objPHPExcel->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

        // Read headers
        $header = [];
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $header[] = trim($sheet->getCellByColumnAndRow($col, 1)->getValue());
        }

        $imported = 0;
        $errors = [];
        $duplicateCount = 0;

        // Mandatory fields
        $mandatoryFields = ['Name *', 'Birth Date * (yyyy-mm-dd)', 'Gender *', 'Nationality *', 'Aadhaar No *'];

        // Helper functions
        function titleCase($str)
        {
            return ucwords(strtolower(trim($str)));
        }
        function fixScientific($val)
        {
            if (is_numeric($val) && stripos($val, 'E') !== false) {
                return number_format($val, 0, '', '');
            }
            return trim($val);
        }
        function limitLength($val, $len)
        {
            return substr(trim($val), 0, $len);
        }
        function yesNoFlag($val)
        {
            $val = strtolower(trim($val));
            return ($val === 'yes' || $val === 'y' || $val === 'on') ? 'Y' : 'N';
        }
        function epsFlag($val)
        {
            $val = strtolower(trim($val));
            return ($val === 'yes' || $val === 'y') ? 'Y' : null;
        }
$validBloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        // --- Fetch existing Aadhaar / PAN once ---
        $existingJoin = $this->EmployeeJoin->find('all', ['fields' => ['id_card', 'pan_no']]);
        $existingDetails = $this->EmployeeDetails->find('all', ['fields' => ['id_card', 'pan_no']]);

        $existingIdCards = [];
        $existingPans = [];
        foreach (array_merge($existingJoin, $existingDetails) as $row) {
            $id_card_val = isset($row['EmployeeJoin']['id_card']) ? $row['EmployeeJoin']['id_card'] : (isset($row['EmployeeDetails']['id_card']) ? $row['EmployeeDetails']['id_card'] : null);
            $pan_val = isset($row['EmployeeJoin']['pan_no']) ? strtoupper($row['EmployeeJoin']['pan_no']) : (isset($row['EmployeeDetails']['pan_no']) ? strtoupper($row['EmployeeDetails']['pan_no']) : null);
            if ($id_card_val) $existingIdCards[] = $id_card_val;
            if ($pan_val) $existingPans[] = $pan_val;
        }

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $cell->setDataType(PHPExcel_Cell_DataType::TYPE_STRING);
                $rowData[$header[$col]] = trim($cell->getValue());
            }

            // Skip empty rows
            if (empty($rowData['Name *']) && empty($rowData['Aadhaar No *'])) {
                continue;
            }

            // --- Check mandatory fields ---
            $missingFields = [];
            foreach ($mandatoryFields as $field) {
                if (empty($rowData[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                $errors[] = [
                    'row' => $row,
                    'error' => 'Mandatory fields missing: ' . implode(', ', $missingFields)
                ];
                continue; // Skip this row
            }
            $birthDate = trim($rowData['Birth Date * (yyyy-mm-dd)']);

            if (!empty($birthDate)) {

                // If Excel serial number (e.g., 45658)
                if (is_numeric($birthDate)){
                    // Convert Excel serial to PHP date
                    $unix_date = ($birthDate - 25569) * 86400;
                    $birth = (new DateTime())->setTimestamp($unix_date);
                } else {
                    // Supported formats
                    $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];

                    $birth = false;
                    foreach ($formats as $format) {
                        $tmp = DateTime::createFromFormat($format, $birthDate);
                        if ($tmp && $tmp->format($format) === $birthDate) {
                            $birth = $tmp;
                            break;
                        }
                    }
                }

                // Invalid date
                if (!$birth) {
                    $errors[] = [
                        'row' => $row,
                        'error' => "Invalid Birth Date format or Excel serial date"
                    ];
                    continue;
                }

                // Calculate age
                $today = new DateTime();
                $age = $birth->diff($today)->y;


                if ($age < 18) {
                    $errors[] = [
                        'row' => $row,
                        'error' => "Age must be above 18 (current age: $age)."
                    ];
                    continue;
                }
            }
            $blood = strtoupper(trim($rowData['Blood Group']));
if (!empty($blood) && !in_array($blood, $validBloodGroups)) {
    $errors[] = [
        'row' => $row,
        'error' => 'Invalid Blood Group format'
    ];
    continue;
}


            // --- Check for invalid special characters ---
            $specialCharPattern = '/[^a-zA-Z0-9\s\-\/,()&+]/';
$invalidFields = [];

foreach ($rowData as $field => $value) {

    if ($value === null || $value === '') continue;

    // Email validation
    if (stripos($field, 'Email') !== false) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $invalidFields[] = "$field (invalid email format)";
        }
        continue;
    }
if (stripos($field, 'Name *') !== false) {
    if (preg_match('/[^a-zA-Z0-9.\s\-]/', $value)) {
        $invalidFields[] = $field;
    }
    continue;
}
    // Address validation (relaxed)
    if (stripos($field, 'Address') !== false) {
        if (preg_match('/[^a-zA-Z0-9\s\-\/,()&.#:\'"]/', $value)) {
            $invalidFields[] = $field;
        }
        continue;
    }

    // Default strict validation
    if (preg_match($specialCharPattern, $value)) {
        $invalidFields[] = $field;
    }
}

if (!empty($invalidFields)) {
    $errors[] = [
        'row' => $row,
        'error' => 'Special characters not allowed in: ' . implode(', ', $invalidFields)
    ];
    continue;
}


            // Fetch all countries for mapping
            $countries = $this->EmployeeDetails->query("SELECT id, country_name, nationality  FROM countries_nationality ORDER BY id ASC");
            $countryMap = [];
            foreach ($countries as $r) {
                $id = $r['countries_nationality']['id'];
                $name = strtolower(trim($r['countries_nationality']['country_name']));
                $code = strtolower(trim($r['countries_nationality']['nationality']));
                $countryMap[$name] = $id;
                $countryMap[$code] = $id;
            }

            // Normalize values
            $name = titleCase($rowData['Name *']);
            $gender = strtolower(trim($rowData['Gender *']));
            $marital = titleCase($rowData['Marital Status']);
            $guardian = titleCase($rowData['Guardian Name']);
            $relation = titleCase($rowData['Relation']);
            $blood = strtoupper(trim($rowData['Blood Group']));
            $state = titleCase($rowData['State']);
            $district = titleCase($rowData['District']);
            $bank = titleCase($rowData['Bank Name']);
            $branch = titleCase($rowData['Branch']);
            $address = titleCase($rowData['Address']);

            // Numeric fixes
            $aadhaar = fixScientific($rowData['Aadhaar No *']);
            $uan = fixScientific($rowData['UAN No']);
            $pf = fixScientific($rowData['PF No']);
            $account = fixScientific($rowData['Account Number']);
            $esi = fixScientific($rowData['ESI No']);
            $wps = fixScientific($rowData['WPS ID']);
            $lwf = fixScientific($rowData['LWF Registration No']);
            $prev_member = fixScientific($rowData['Previous Member ID']);
            $ifsc = strtoupper(trim($rowData['IFSC Code']));

            $eps_flag = epsFlag($rowData['EPS Eligibility (Yes/No)']);
            // $phy_flag = yesNoFlag($rowData['Physical Handicap (Yes/No)']);
            $int_flag = yesNoFlag($rowData['International Worker (Yes/No)']);
            $locomotive_flag = yesNoFlag($rowData['Locomotive (Yes/No)']);
            $hearing_flag = yesNoFlag($rowData['Hearing (Yes/No)']);
            $visual_flag = yesNoFlag($rowData['Visual (Yes/No)']);
            if ($locomotive_flag === 'Yes' || $hearing_flag === 'Yes' || $visual_flag === 'Yes') {
                $phy_flag = 'Yes';
            } else {
                $phy_flag = yesNoFlag($rowData['Physical Handicap (Yes/No)']);
            }

            // Country mapping
            $countryOriginInput = strtolower(trim($rowData['Country of Origin']));
            $nationalityInput = strtolower(trim($rowData['Nationality *']));
            $country_origin = isset($countryMap[$countryOriginInput]) ? $countryMap[$countryOriginInput] : null;
            $nationality_id = isset($countryMap[$nationalityInput]) ? $countryMap[$nationalityInput] : null;

            // Date conversion
            $dobValue = $rowData['Birth Date * (yyyy-mm-dd)'];
            if (is_numeric($dobValue)) {
                $date_of_birth = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($dobValue));
            } else {
                $date_of_birth = !empty($dobValue) ? date('Y-m-d', strtotime($dobValue)) : null;
            }

            $id_card = limitLength($aadhaar, 12);
          $pan_no = !empty($rowData['PAN No'])
    ? strtoupper(limitLength($rowData['PAN No'], 10))
    : null;

            // // --- Check duplicates ---
            // if (in_array($id_card, $existingIdCards) || in_array($pan_no, $existingPans)) {
            //     $errors[] = ['row' => $row, 'error' => 'Duplicate ID Card or PAN No. Row skipped.'];
            //     $duplicateCount++;
            //     continue;
            // }

            // --- Check duplicates separately ---
            $duplicateMsg = [];
            if (in_array($id_card, $existingIdCards)) {
                $duplicateMsg[] = "Duplicate Aadhaar No.";
            }
            if (in_array($pan_no, $existingPans)) {
                $duplicateMsg[] = "Duplicate PAN No.";
            }

          if (!empty($duplicateMsg)) {

    $errorText = implode(' and ', $duplicateMsg) . " in Row " . ($row - 1) . " skipped.";
$errors[] = [
    'row' => $row,
    'error' => $errorText ."<br>"
];


    if (in_array($id_card, $existingIdCards)) $duplicateCount++;
    if (in_array($pan_no, $existingPans)) $duplicateCount++;

    continue;
}


            // Prepare data
            $saveData = [
                'EmployeeJoin' => [
                    'first_name' => limitLength($name, 50),
                    'date_of_birth' => $date_of_birth,
                    'classification' => limitLength($gender, 10),
                    'email' => strtolower(limitLength($rowData['Email Address'], 100)),
                    'mobile_no' => limitLength($rowData['Phone Number'], 15),
                    'address' => limitLength($address, 255),
                    'district' => limitLength($district, 50),
                    'pincode' => limitLength($rowData['Pin Code'], 10),
                    'blood' => limitLength($blood, 5),
                    'maritual_status' => limitLength($marital, 15),
                    'guradian' => limitLength($guardian, 50),
                    'relation_guardian' => limitLength($relation, 50),
                    'state' => limitLength($state, 50),
                    'bank' => limitLength($bank, 50),
                    'bank_branch' => limitLength($branch, 50),
                    'ifsc_code' => limitLength($ifsc, 20),
                    'account_no' => limitLength($account, 25),
                    'pf' => limitLength($pf, 25),
                    'id_card' => $id_card,
                    'company_pf' => limitLength($uan, 25),
                    'previous_member_id' => limitLength($prev_member, 20),
                    'esi' => limitLength($esi, 15),
                    'esi_dispensary' => limitLength($rowData['ESI Dispensary'], 50),
                    'wps_code' => limitLength($wps, 20),
                    'lwf_code' => limitLength($lwf, 20),
                    'pan_no' => $pan_no,
                    'eps' => $eps_flag,
                    'status' => 1,
                    'physical_handicap' => $phy_flag,
                    'international_worker' => $int_flag,
                    'locomotive' => $locomotive_flag,
                    'hearing' => $hearing_flag,
                    'visual' => $visual_flag,
                    'nationality_id' => $nationality_id,
                    'country_origin' => $country_origin,
                ]
            ];

            $this->EmployeeJoin->create();
            if ($this->EmployeeJoin->save($saveData)) {
                $imported++;
                $existingIdCards[] = $id_card;

if (!empty($pan_no)) {
    $existingPans[] = $pan_no;
}
            } else {
                $errors[] = ['row' => $row, 'error' => 'Database save failed'];
            }
        }

        unlink($targetpath);

        // ---- Dynamic Message ----
        // $messageParts = [];
        // if ($imported > 0) $messageParts[] = "$imported record" . ($imported > 1 ? 's' : '') . " imported successfully";
        // if ($duplicateCount > 0) $messageParts[] = count($errors) . " duplicate" . (count($errors) > 1 ?  's were' : ' was') . " skipped,";
        // if (!empty($errors)) $messageParts[] =" and ". $duplicateCount . " error" . ($duplicateCount > 1 ? 's' : '') . " occurred.";

        // $msg = !empty($messageParts) ? implode("\n", $messageParts) : 'No records processed.';

// ---- Dynamic Message ----
$messageParts = [];

if ($imported > 0) {
    $messageParts[] = $imported . ' record' . ($imported > 1 ? 's' : '') . ' imported successfully';
}

if ($duplicateCount > 0) {
    $messageParts[] = $duplicateCount . ' duplicate record' . ($duplicateCount > 1 ? 's' : '') . ' skipped';
}

$errorCount = count($errors);
if ($errorCount > 0) {
    $messageParts[] = $errorCount . ' error' . ($errorCount > 1 ? 's' : '') . ' found';
}

$msg = !empty($messageParts)
    ? implode(' and ', $messageParts)
    : 'No records processed.';

        $response = [
            'success' => empty($errors) ? 1 : 0,
            'msg' => $msg,
            'imported_count' => $imported,
            'duplicate_count' => $duplicateCount,
            'error_count' => count($errors),
        ];

    if (!empty($errors)) {
  $response['errors'] = rtrim(
    implode(array_map(function ($e) {
        return $e['error'];
    }, $errors)),
    "<br>"
);
}

        echo json_encode($response);
        exit;
    }



    public function deleteJoin()
    {
        $this->autoRender = false;
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');


        $emp_pkey = $this->request->data('emp_pkey');

        if (empty($emp_pkey)) {
            echo json_encode(['status' => 'error', 'message' => 'Employee key missing']);
            return;
        }

        $this->loadModel('EmployeeJoin'); // your emp_join table model

        if ($this->EmployeeJoin->deleteAll(['emp_join_pkey' => $emp_pkey])) {
            echo json_encode(['status' => 'success', 'message' => 'Employee deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
        }
    }
      public function checkAccountNo()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $account_no = isset($this->request->data['account_no']) ? trim($this->request->data['account_no']) : '';
        $emp_pkey = isset($this->request->data['emp_pkey']) ? $this->request->data['emp_pkey'] : null;

        $status = 'error';
        $message = '';
        $color = 'red';

        if (empty($account_no)) {
            $message = 'Account number is required!';
        } elseif (!ctype_digit($account_no)) {
            $message = 'Account number must contain only digits!';
        } else {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            // $conditions = ['account_no' => $account_no];
            $conditions = [
    'EmployeeDetails.account_no' => $account_no,
    'EmployeeDetails.status' => 1
];
            if (!empty($emp_pkey)) {
                $conditions['emp_pkey !='] = $emp_pkey;
            }

            $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => $conditions]);

            if (!empty($checkDetails)) {
                $status = 'duplicate';
                $message = 'Account number already exists!';
                $color = 'red';
            } else {
                $status = 'valid';
                $message = 'Account number is valid.';
                $color = 'green';
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'color' => $color]);
        exit;
    }

    public function checkPF()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $pf = isset($this->request->data['pf']) ? trim($this->request->data['pf']) : '';
        $emp_pkey = isset($this->request->data['emp_pkey']) ? $this->request->data['emp_pkey'] : null;

        $status = 'error';
        $message = '';
        $color = 'red';

        if (empty($pf)) {
            $message = 'PF number is required!';
        } elseif (!preg_match('/^[A-Za-z0-9]+$/', $pf)) {
            $message = 'PF number must be alphanumeric!';
        } else {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            // $conditions = ['pf' => $pf];
            $conditions = [
    'EmployeeDetails.company_pf' => $pf,
    'EmployeeDetails.status' => 1
];
            if (!empty($emp_pkey)) {
                $conditions['emp_pkey !='] = $emp_pkey;
            }

            $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => $conditions]);

            if (!empty($checkDetails)) {
                $status = 'duplicate';
                $message = 'PF number already exists!';
                $color = 'red';
            } else {
                $status = 'valid';
                $message = 'PF number is valid.';
                $color = 'green';
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'color' => $color]);
        exit;
    }

    public function checkIdCard()
    {
        $this->autoRender = false;
        $this->response->type('json');  // Set JSON response type

        $id_card = isset($this->request->data['id_card']) ? trim($this->request->data['id_card']) : '';
        $emp_pkey = isset($this->request->data['emp_pkey']) ? $this->request->data['emp_pkey'] : null;

        $status = 'error';
        $message = '';
        $color = '';

        if (empty($id_card)) {
            $message = 'Aadhaar number is required!';
        } elseif (!preg_match('/^\d{12}$/', $id_card)) {
            $message = 'Aadhaar must be exactly 12 digits!';
        } else {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
$conditions = [
    'EmployeeDetails.id_card' => $id_card,
    'EmployeeDetails.status' => 1
];

            if (!empty($emp_pkey)) {
                // Exclude current record from duplicate check
                $conditions['EmployeeDetails.emp_pkey !='] = $emp_pkey;
            }

            $checkDetails = $this->EmployeeDetails->find('first', [
                'conditions' => $conditions
            ]);

            if (!empty($checkDetails)) {
                $status = 'duplicate';
                $message = 'Aadhaar number already exists!';
                $color = 'red';
            } else {
                $status = 'valid';
                $message = 'Aadhaar number is valid.';
                $color = 'green';
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'color' => $color]);
        exit;
    }


    // public function checkIdCard()
    // {
    //     $this->autoRender = false;

    //     $id_card = isset($this->request->data['id_card']) ? trim($this->request->data['id_card']) : '';
    //     $status = 'error';
    //     $message = '';

    //     if (empty($id_card)) {
    //         $message = 'Aadhaar number is required!';
    //     } elseif (!preg_match('/^\d{12}$/', $id_card)) {
    //         $message = 'Aadhaar must be exactly 12 digits!';
    //     } else {
    //         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    //         $checkDetails = $this->EmployeeDetails->find('first', [
    //             'conditions' => ['EmployeeDetails.id_card' => $id_card]
    //         ]);

    //         if (!empty($checkDetails)) {
    //             $status = 'duplicate';
    //             $message = 'Aadhaar number already exists!';
    //             $color = 'red';
    //         } else {
    //             $status = 'valid';
    //             $message = 'Aadhaar number is valid.';
    //             $color = 'green';
    //         }
    //     }

    //     echo json_encode(['status' => $status, 'message' => $message,'color' => $color]);
    //     exit;
    // }
    public function checkPan()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $pan_no = isset($this->request->data['pan_no']) ? strtoupper(trim($this->request->data['pan_no'])) : '';
        $emp_pkey = isset($this->request->data['emp_pkey']) ? $this->request->data['emp_pkey'] : null;

        $status = 'error';
        $message = '';
        $color = 'red';

        if (empty($pan_no)) {
            $message = 'PAN number is required!';
        } elseif (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan_no)) {
            $message = 'Invalid PAN format!';
        } else {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $conditions = [
            'EmployeeDetails.pan_no' => $pan_no,
            'EmployeeDetails.status' => 1
        ];
            if (!empty($emp_pkey)) {
                $conditions['emp_pkey !='] = $emp_pkey;
            }

            $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => $conditions]);

            if (!empty($checkDetails)) {
                $status = 'duplicate';
                $message = 'PAN number already exists!';
                $color = 'red';
            } else {
                $status = 'valid';
                $message = 'PAN number is valid.';
                $color = 'green';
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'color' => $color]);
        exit;
    }

    public function checkESI()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $esi = isset($this->request->data['esi']) ? trim($this->request->data['esi']) : '';
        $emp_pkey = isset($this->request->data['emp_pkey']) ? $this->request->data['emp_pkey'] : null;

        $status = 'error';
        $message = 'ESI number is required!';
        $color = 'red';

        if (!empty($esi)) {
            if (!preg_match('/^\d{10}$/', $esi)) {
                $message = 'ESI number must be exactly 10 digits!';
            } else {
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

                $conditions = [
    'EmployeeDetails.esi' => $esi,
    'EmployeeDetails.status' => 1,
];
                if (!empty($emp_pkey)) {
                    $conditions['emp_pkey !='] = $emp_pkey;
                }

                $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => $conditions]);

                if (!empty($checkDetails)) {
                    $status = 'duplicate';
                    $message = 'ESI number already exists!';
                    $color = 'red';
                } else {
                    $status = 'valid';
                    $message = 'ESI number is valid.';
                    $color = 'green';
                }
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'color' => $color]);
        exit;
    }

    public function checkLWF()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $lwf_code = isset($this->request->data['lwf_code']) ? trim($this->request->data['lwf_code']) : '';
        $emp_pkey = isset($this->request->data['emp_pkey']) ? $this->request->data['emp_pkey'] : null;

        $status = 'error';
        $message = 'LWF code is required!';
        $color = 'red';

        if (!empty($lwf_code)) {
            if (!preg_match('/^[A-Za-z0-9]{5,15}$/', $lwf_code)) {
                $message = 'LWF code must be 5–15 alphanumeric characters!';
            } else {
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

                $conditions = ['lwf_code' => $lwf_code];
                if (!empty($emp_pkey)) {
                    $conditions['emp_pkey !='] = $emp_pkey;
                }

                $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => $conditions]);

                if (!empty($checkDetails)) {
                    $status = 'duplicate';
                    $message = 'LWF code already exists!';
                    $color = 'red';
                } else {
                    $status = 'valid';
                    $message = 'LWF code is valid.';
                    $color = 'green';
                }
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'color' => $color]);
        exit;
    }

    public function checkUAN()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $uan = isset($this->request->data['uan']) ? trim($this->request->data['uan']) : '';
        $emp_pkey = isset($this->request->data['emp_pkey']) ? $this->request->data['emp_pkey'] : null;

        $status = 'error';
        $message = '';
        $color = 'red';

        if (empty($uan)) {
            $message = 'UAN is required!';
        } elseif (!preg_match('/^\d{12}$/', $uan)) {
            $message = 'UAN must be exactly 12 digits!';
        } else {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $conditions = [
    'EmployeeDetails.pf' => $uan,
    'EmployeeDetails.status' => 1,
];

            if (!empty($emp_pkey)) {
                $conditions['emp_pkey !='] = $emp_pkey;
            }

            $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => $conditions]);

            if (!empty($checkDetails)) {
                $status = 'duplicate';
                $message = 'UAN already exists!';
                $color = 'red';
            } else {
                $status = 'valid';
                $message = 'UAN is valid.';
                $color = 'green';
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'color' => $color]);
        exit;
    }


    // Check PAN
    // ---------------- PAN ----------------
    //     public function checkPan()
    // {
    //     $this->autoRender = false;
    //     $pan_no = isset($this->request->data['pan_no']) ? strtoupper(trim($this->request->data['pan_no'])) : '';

    //     $status = 'error';
    //     $message = '';
    //     $color = 'red'; // Default color for error

    //     if (empty($pan_no)) {
    //         $message = 'PAN number is required!';
    //     } elseif (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan_no)) {
    //         $message = 'Invalid PAN format!';
    //     } else {
    //         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    //         $checkDetails = $this->EmployeeDetails->find('first', [
    //             'conditions' => ['pan_no' => $pan_no]
    //         ]);

    //         if (!empty($checkDetails)) {
    //             $status = 'duplicate';
    //             $message = 'PAN number already exists!';
    //             $color = 'red'; // 🔴 Red for duplicate
    //         } else {
    //             $status = 'valid';
    //             $message = 'PAN number is valid.';
    //             $color = 'green'; // 🟢 Green for valid
    //         }
    //     }

    //     echo json_encode([
    //         'status' => $status,
    //         'message' => $message,
    //         'color' => $color
    //     ]);
    //     exit;
    // }


    //     public function checkESI()
    //     {
    //         $this->autoRender = false;
    //         $esi = isset($this->request->data['esi']) ? trim($this->request->data['esi']) : '';

    //         $status = 'error';
    //         $message = 'ESI number is required!';

    //         if (!empty($esi)) {
    //             if (!preg_match('/^\d{10}$/', $esi)) {
    //                 $message = 'ESI number must be exactly 10 digits!';
    //             } else {
    //                 $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
    //                 $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    //                 // $checkJoin = $this->EmployeeJoin->find('first', ['conditions' => ['esi' => $esi]]);
    //                 $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => ['esi' => $esi]]);

    //                 if (!empty($checkDetails)) {
    //                     $status = 'duplicate';
    //                     $message = 'ESI number already exists!';
    //                 } else {
    //                     $status = 'valid';
    //                     $message = 'ESI number is valid.';
    //                 }
    //             }
    //         }

    //         echo json_encode(['status' => $status, 'message' => $message]);
    //         exit;
    //     }


    //     // ---------------- LWF ----------------
    //     public function checkLWF()
    //     {
    //         $this->autoRender = false;
    //         $lwf_code = isset($this->request->data['lwf_code']) ? trim($this->request->data['lwf_code']) : '';

    //         $status = 'error';
    //         $message = 'LWF code is required!';

    //         if (!empty($lwf_code)) {
    //             if (!preg_match('/^[A-Za-z0-9]{5,15}$/', $lwf_code)) {
    //                 $message = 'LWF code must be 5–15 alphanumeric characters!';
    //             } else {
    //                 $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
    //                 $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    //                 // $checkJoin = $this->EmployeeJoin->find('first', ['conditions' => ['lwf_code' => $lwf_code]]);
    //                 $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => ['lwf_code' => $lwf_code]]);

    //                 if (!empty($checkDetails)) {
    //                     $status = 'duplicate';
    //                     $message = 'LWF code already exists!';
    //                 } else {
    //                     $status = 'valid';
    //                     $message = 'LWF code is valid.';
    //                 }
    //             }
    //         }

    //         echo json_encode(['status' => $status, 'message' => $message]);
    //         exit;
    //     }
    //     public function checkUAN()
    //     {
    //         $this->autoRender = false;
    //         $uan = isset($this->request->data['uan']) ? trim($this->request->data['uan']) : '';

    //         $status = 'error';
    //         $message = '';

    //         if (empty($uan)) {
    //             $message = 'UAN is required!';
    //         } elseif (!preg_match('/^\d{12}$/', $uan)) {
    //             $message = 'UAN must be exactly 12 digits!';
    //         } else {

    //             $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    //             $checkDetails = $this->EmployeeDetails->find('first', ['conditions' => ['company_pf' => $uan]]);

    //             if (!empty($checkDetails)) {
    //                 $status = 'duplicate';
    //                 $message = 'UAN already exists!';
    //             } else {
    //                 $status = 'valid';
    //                 $message = 'UAN is valid.';
    //             }
    //         }

    //         echo json_encode(['status' => $status, 'message' => $message]);
    //         exit;
    //     }
    // Generic function to check emp_join first, then employee_details
    public function getEducationData($emp_join_fkey = null)
    {
        $this->autoRender = false;

        if (!$emp_join_fkey) {
            echo json_encode([]);
            return;
        }

        $this->Education->useDbConfig = $this->Session->read('ds');

        $educationData = $this->Education->find('all', [
            'conditions' => ['Education.emp_join_fkey' => $emp_join_fkey, 'Education.status' => 1],
            'fields' => ['course', 'university', 'duration', 'mark']
        ]);

        $result = array_map(function ($row) {
            return $row['Education'];
        }, $educationData);

        echo json_encode($result);
    }

    public function getExperienceData($emp_join_fkey = null)
    {
        $this->autoRender = false;

        if (!$emp_join_fkey) {
            echo json_encode([]);
            return;
        }

        $this->WorkExperience->useDbConfig = $this->Session->read('ds');

        $experienceData = $this->WorkExperience->find('all', [
            'conditions' => ['WorkExperience.emp_join_fkey' => $emp_join_fkey, 'WorkExperience.status' => 1],
            'fields' => ['company', 'designation', 'department', 'salary', 'from_date', 'to_date']
        ]);

        $result = array_map(function ($row) {
            return $row['WorkExperience'];
        }, $experienceData);

        echo json_encode($result);
    }

    public function getFamilyData($emp_join_fkey = null)
    {
        $this->autoRender = false;

        if (!$emp_join_fkey) {
            echo json_encode([]);
            return;
        }

        $this->EmpFam->useDbConfig = $this->Session->read('ds');

        $familyData = $this->EmpFam->find('all', [
            'conditions' => ['EmpFam.emp_join_fkey' => $emp_join_fkey, 'EmpFam.status' => 1],
            'fields' => ['name', 'DOB', 'gender', 'blood_group', 'relation', 'emergency_contact', 'nationality', 'contact_number', 'alternate_number', 'is_nominee']
        ]);

        $result = array_map(function ($row) {
            return $row['EmpFam'];
        }, $familyData);

        echo json_encode($result);
    }

    public function getDocumentData($emp_join_fkey = null)
    {
        $this->autoRender = false;

        if (!$emp_join_fkey) {
            echo json_encode([]);
            return;
        }

        $this->EmpDocument->useDbConfig = $this->Session->read('ds');

        $docData = $this->EmpDocument->find('all', [
            'conditions' => ['EmpDocument.emp_join_fkey' => $emp_join_fkey, 'EmpDocument.status' => 1],
            'fields' => ['name', 'document_type', 'document_number', 'relation', 'valid_from', 'valid_till', 'classification', 'nationality', 'files', 'remind']
        ]);

        $result = array_map(function ($row) {
            return $row['EmpDocument'];
        }, $docData);
        // debug($docData);
        echo json_encode($result);
    }

    //end
}

require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

class MYPDF extends TCPDF
{
    //Edited by Akshay on17-10-2024
    // Page header
    public function Header()
    {
        // Get the page width
        $pageWidth = $this->getPageWidth();

        // Set header color
        $this->SetTextColor(0, 64, 255);

        // Set logo (assuming $this->header_logo is set by SetHeaderData)
        if ($this->header_logo) {
            //  $logoWidth = 25; // Logo width
            $logoSize = 20; // Logo width and height for square shape
            $logoX = 170; // Increase this value to move the logo further to the right
            $logoY = 5;   // header_logo position change    edited bu anukrishnan_06-02-2025
            $this->Image($this->header_logo, $logoX, $logoY, $logoSize);
        }

        // Set font for the company name
        $this->SetFont('helvetica', 'B', 12);

        // Calculate logo and company name positions
        $this->SetY(16); // Set Y position for header
        $this->SetX(13); // Set the starting X position to move left (reduce this value for more leftward movement)
        $this->Cell(1, 6, '', 0, 0); // Placeholder cell for logo width
        $this->Cell(0, 4, COMPANY_NAME, 0, 1); // Company name next to the logo


        // Set font for URLs
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 4, COMPANY_URLS, 0, 1); // Centered URLs

        // Draw a line under the header
        $this->Line(10, $this->GetY(), $pageWidth - 10, $this->GetY()); // Draw a line from left to right
    }

    //End


    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        date_default_timezone_set("Asia/Calcutta");
        // Page number
        $this->Cell(0, 10, 'Downloaded from Mypayrollmaster Admin at ' . date("d-m-Y h:i a") . '            Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
