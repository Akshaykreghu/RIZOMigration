<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryStructureDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SalaryStructureDetails';
	public $primaryKey = 'structure_det_id';
    public $useTable = 'salary_structure_details';
	
	
}