<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class item_master extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'item_master';
	public $primaryKey = 'item_pkey';
    public $useTable = 'item_master';
	
}