<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryHikeDetail extends AppModel
{
	/**
	 * Primary key field
	 *
	 * @var string
	 */
	public $name = 'SalaryHikeDetail';
	public $primaryKey = 'salary_hike_details_pkey';
	public $useTable = 'salary_hike_details';
}
