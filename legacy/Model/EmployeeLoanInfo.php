<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeLoanInfo extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeLoanInfo';
	public $primaryKey = 'emp_loan_info_pkey';
    public $useTable = 'emp_loan_info';
}