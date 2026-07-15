<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Nationality extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Nationality';
    public $useTable = 'countries_nationality';
	 public $useDbConfig = 'controldb';
}
