<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class EmployeeJoin extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmployeeJoin';
    public $primaryKey = 'emp_join_pkey';
    public $useTable = 'emp_join';
    public $virtualFields = array('emp_name' => 'CONCAT(EmployeeJoin.first_name, " ", EmployeeJoin.last_name)');
    public $order = "EmployeeJoin.emp_name ASC";

    /* public $hasOne = array(
      'EmployeeProfessionalDetails' => array(
      'className' => 'EmployeeProfessionalDetails'
      )
      ); */
}
