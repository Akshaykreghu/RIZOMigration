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

App::uses('ConnectionManager', 'Model','Organization');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class CompoffController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Compoff';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'LeaveRequests');

        public function index(){
            $my_pkey = $this->Session->read('emp_fkey');
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
            $arr_empleaverequests = $this->LeaveRequests->query("select employee_info.*,emp_name,month_year,count(*) as compoff
                        from attendance_register,fin_year fy left join employee_info on(employee_info.emp_pkey = '$my_pkey')
                         where (FIELD1='COFF' OR FIELD2='COFF' OR FIELD3='COFF' OR FIELD4='COFF' 
                         OR FIELD5='COFF' OR FIELD6='COFF' OR FIELD7='COFF' OR FIELD8='COFF' OR 
                         FIELD9='COFF' OR FIELD10='COFF' OR FIELD11='COFF' OR FIELD12='COFF' OR 
                         FIELD13='COFF' OR FIELD14='COFF' OR FIELD15='COFF' OR FIELD16='COFF' OR 
                         FIELD17='COFF' OR FIELD18='COFF' OR FIELD19='COFF' OR FIELD20='COFF' OR 
                         FIELD21='COFF' OR FIELD22='COFF' OR FIELD23='COFF' OR FIELD24='COFF' OR 
                         FIELD25='COFF' OR FIELD26='COFF' OR FIELD27='COFF' OR FIELD28='COFF' OR 
                         FIELD29='COFF' OR FIELD30='COFF' OR FIELD31='COFF' OR FIELD32='COFF')
                        and isdelete='N' and concat(month_year,'-01') between fy.start_month and 
                        end_month and fy.Year_status='OPEN' and attendance_register.emp_fkey = '$my_pkey' group by emp_name,month_year");
                
                    $arr_empleave_eligiility = $this->LeaveRequests->query("select emp_pkey ,yearmonth ,count(*) as eligibility from
                        emp_detail_timeattandance, fin_year fy where (weekoff is not null or holiday is not null) and present='P/P'
                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN' and emp_pkey = '$my_pkey'
                         group by emp_pkey ,yearmonth 
                        union all
                        select emp_pkey ,yearmonth ,count(*)*.5 from
                        emp_detail_timeattandance , fin_year fy where (weekoff is not null or holiday is not null) and present in('P/A','A/P')
                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN'
                        group by emp_pkey ,yearmonth");
          $arr_leavepolicydetails_for_template[] = array(
                    'summary' => $arr_empleaverequests,
                    'eligibility' => $arr_empleave_eligiility,
                );
          $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
    }
}
?>
