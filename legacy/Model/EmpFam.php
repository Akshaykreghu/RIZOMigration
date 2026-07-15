<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class EmpFam extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmpFam';
    public $primaryKey = 'emp_family_pkey';
    public $useTable = 'family';

    /* public $hasOne = array(
      'EmployeeProfessionalDetails' => array(
      'className' => 'EmployeeProfessionalDetails'
      )
      ); */
}