<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class LeavePolicyGroup extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'LeavePolicyGroup';
	public $primaryKey = 'LEAVEPOLICY_GROUP_ID';
    public $useTable = 'leavepolicy_group';
	
}