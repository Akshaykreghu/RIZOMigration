<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeTaxTransactions extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeTaxTransactions';
	public $primaryKey = 'emp_tax_tran_id';
    public $useTable = 'emp_tax_transactions';
}