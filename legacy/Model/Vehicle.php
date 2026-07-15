<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Vehicle extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Vehicle';
	public $primaryKey = 'vehicle_master_pkey';
    public $useTable = 'vehicle_master';
	
	
}