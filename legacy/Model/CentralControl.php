<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class CentralControl extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'CentralControl';
	public $primaryKey = 'control_pkey';
    public $useTable = 'central_control';
    public $useDbConfig = 'controldb';
	public $tablePrefix = '';
}
