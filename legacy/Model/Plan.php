<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Plan extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Plan';
	public $primaryKey = 'plan_id';
    public $useTable = 'plans';
    public $useDbConfig = 'controldb';
	public $tablePrefix = '';
}
