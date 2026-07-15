<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class Education extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'Education';
    public $primaryKey = 'education_pkey';
    public $useTable = 'Education';

    /* public $hasOne = array(
      'EmployeeProfessionalDetails' => array(
      'className' => 'EmployeeProfessionalDetails'
      )
      ); */
}
