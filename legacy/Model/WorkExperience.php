<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class WorkExperience extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'WorkExperience';
    public $primaryKey = 'experience_pkey';
    public $useTable = 'work_experience';

    /* public $hasOne = array(
      'EmployeeProfessionalDetails' => array(
      'className' => 'EmployeeProfessionalDetails'
      )
      ); */
}
