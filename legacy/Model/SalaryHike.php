<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryHike extends AppModel
{
	/**
	 * Primary key field
	 *
	 * @var string
	 */
	public $name = 'SalaryHike';
	public $primaryKey = 'salary_hike_pkey';
	public $useTable = 'salary_hike';

}
