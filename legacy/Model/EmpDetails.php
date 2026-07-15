<?php
App::uses('AppModel', 'Model');
/**
 * Emp Details Model
 *
 */
class EmpDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmpDetails';
	public $primaryKey = 'emp_pkey';
    public $useTable = 'emp_details';
	
}
