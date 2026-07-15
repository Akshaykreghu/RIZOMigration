<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Allocate_expense extends AppModel{
	/**
 * Primary key field
 *
 * @var string
 */ 
	public $name = 'Allocate_expense';
	public $useTable = 'allocate_expense';
	public $primaryKey = 'allocate_expense_pkey';
	
}