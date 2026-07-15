<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeLeaveInfo extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeLeaveInfo';
	public $primaryKey = 'emp_leave_info';
    public $useTable = 'emp_leave_info';
}