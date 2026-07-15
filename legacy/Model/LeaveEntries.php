<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class LeaveEntries extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'LeaveEntries';
	public $primaryKey = 'LEAVEENTRYID';
    public $useTable = 'leaveentries';
	
}