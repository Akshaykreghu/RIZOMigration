<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class ComponentIncrement extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'ComponentIncrement';
    public $useTable = 'component_increment';
	public $primaryKey = 'sal_pkey';
}
