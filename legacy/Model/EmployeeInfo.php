<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class EmployeeInfo extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmployeeInfo';
    public $primaryKey = 'emp_pkey';
    public $useTable = 'employee_info';
   // public $virtualFields = array('emp_name' => 'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name)');
    public $order = "EmployeeInfo.EmpName ASC";

    /* public $hasOne = array(
      'EmployeeProfessionalDetails' => array(
      'className' => 'EmployeeProfessionalDetails'
      )
      ); */
}
