<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryIncrementDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SalaryIncrementDetails';
    public $useTable = 'salary_increment_details';
	public $primaryKey = 'salary_increment_details_pkey';
}
