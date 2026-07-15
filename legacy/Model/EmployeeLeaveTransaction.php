<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeLeaveTransaction extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeLeaveTransaction';
	public $primaryKey = 'emp_leave_transactions_pkey';
    public $useTable = 'emp_leave_transactions';
}