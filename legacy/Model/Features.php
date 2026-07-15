<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Features extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Features';
	public $primaryKey = 'feature_id';
    public $useTable = 'features';
    public $useDbConfig = 'controldb';
	public $tablePrefix = '';
}
