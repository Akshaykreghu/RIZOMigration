<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Registrations extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Registrations';
    public $useTable = 'registrations';
    public $useDbConfig = 'controldb';
	public $tablePrefix = '';
}
