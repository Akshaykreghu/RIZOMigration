<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeStructure extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeStructure';
    public $useTable = 'emp_structure';
}
