<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryStructures extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SalaryStructures';
	public $primaryKey = 'structure_id';
    public $useTable = 'salary_structure';
	
}