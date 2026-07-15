<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class TaxSave extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'TaxSave';
	public $primaryKey = 'emp_fkey';
    public $useTable = 'taxes_save';
	
/*
    public $hasMany = array(
        'SalaryHeadItems' => array(
            'className' => 'SalaryHeadItems'
        )
    );*/

}