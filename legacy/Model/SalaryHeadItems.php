<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryHeadItems extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SalaryHeadItems';
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