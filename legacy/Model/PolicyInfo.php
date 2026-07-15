<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class PolicyInfo extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
 
 
	public $primaryKey = 'policy_key';
    public $name = 'PolicyInfo';
    public $useTable = 'policy_info';
}
