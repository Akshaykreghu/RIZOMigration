<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class EmployeeDetails extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmployeeDetails';
    public $primaryKey = 'emp_pkey';
    public $useTable = 'emp_details';
    public $virtualFields = array('emp_name' => 'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name)');
    public $order = "EmployeeDetails.emp_name ASC";

    /* public $hasOne = array(
      'EmployeeProfessionalDetails' => array(
      'className' => 'EmployeeProfessionalDetails'
      )
      ); */
}
