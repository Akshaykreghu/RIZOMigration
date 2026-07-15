<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Currency extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Currency';
    public $useTable = 'countries';
	 public $useDbConfig = 'controldb';
}
