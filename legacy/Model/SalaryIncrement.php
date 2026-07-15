<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryIncrement extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SalaryIncrement';
    public $useTable = 'salary_increment';
	public $primaryKey = 'salary_increment_pkey';
}
