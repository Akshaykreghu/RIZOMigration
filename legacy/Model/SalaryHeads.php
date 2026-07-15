<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SalaryHeads extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SalaryHeads';
	public $primaryKey = 'head_pkey';
    public $useTable = 'salary_heads';
	
/*
    public $hasMany = array(
        'SalaryHeadItems' => array(
            'className' => 'SalaryHeadItems'
        )
    );*/

}