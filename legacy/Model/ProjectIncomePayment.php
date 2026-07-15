<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class ProjectIncomePayment extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'ProjectIncomePayment';
    public $primaryKey = 'income_pkey';
    public $useTable = 'project_income_payment';
	
	
}