<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Country extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Country';
    public $useTable = 'countries';
	 public $useDbConfig = 'controldb';
}
