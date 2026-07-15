<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class LeaveType extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'LeaveType';
	public $primaryKey = 'salary_head_item_pkey';
    public $useTable = 'salary_head_items';
	
	/*
	public $belongsTo = array(
			   'SalaryHeads' => array(
				   'className' => 'SalaryHeads',
				'foreignKey' => 'head_fkey'
			)
		 );*/
	
}