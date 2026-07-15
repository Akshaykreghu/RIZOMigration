<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeAdvance extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name ='EmployeeAdvance';
    public $primaryKey ='emp_advance_pkey';
    public $useTable ='emp_advance';
}