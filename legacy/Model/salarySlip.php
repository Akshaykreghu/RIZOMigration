<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class salarySlip extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'salarySlip';
    public $useTable = 'emp_salary_slip';
	public $primaryKey = 'emp_salary_slip_pkey';
}
