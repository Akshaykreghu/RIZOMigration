<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class item_allocate extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'item_allocate';
	public $primaryKey = 'allocation_pkey';
    public $useTable = 'itm_allocation';
	
}