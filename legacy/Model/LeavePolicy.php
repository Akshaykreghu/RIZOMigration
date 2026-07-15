<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class LeavePolicy extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'LeavePolicy';
	public $primaryKey = 'LEAVEPOLICYID';
    public $useTable = 'leavepolicy';
	
}