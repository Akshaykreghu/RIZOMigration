<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class PlanFeature extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'PlanFeature';
	public $primaryKey = 'id';
    public $useTable = 'plan_features';
    public $useDbConfig = 'controldb';
	public $tablePrefix = '';
}
