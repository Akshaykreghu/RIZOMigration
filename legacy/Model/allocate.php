<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class allocate extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'allocate';
    public $useTable = 'asset_allocate';
	public $primaryKey = 'allocate_pkey';
}
