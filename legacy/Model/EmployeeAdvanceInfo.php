<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeAdvanceInfo extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeAdvanceInfo';
	public $primaryKey = 'emp_advance_info_pkey';
    public $useTable = 'emp_advance_info';
}