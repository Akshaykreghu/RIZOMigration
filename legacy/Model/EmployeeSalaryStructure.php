<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeSalaryStructure extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeSalaryStructure';
    public $useTable = 'emp_salary_structure';
	public $primaryKey = 'emp_salary_structure_pkey';
}
