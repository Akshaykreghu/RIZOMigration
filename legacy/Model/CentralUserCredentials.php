<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class CentralUserCredentials extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'CentralUserCredentials';
	public $primaryKey = 'user_pkey';
    public $useTable = 'user_credentials';
	public $tablePrefix = '';
	public $useDbConfig = 'controldb';
}
