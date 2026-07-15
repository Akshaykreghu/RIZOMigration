<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeePayInfo extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeePayInfo';
	public $primaryKey = 'emp_pay_info_pkey';
    public $useTable = 'emp_pay_info';
}