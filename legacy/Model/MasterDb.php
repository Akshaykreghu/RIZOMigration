<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class MasterDb extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'MasterDb';
    public $useTable = 'master_db';
	 public $useDbConfig = 'controldb';
}
