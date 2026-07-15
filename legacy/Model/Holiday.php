<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Holiday extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Holiday';
	public $primaryKey = 'HOLIDAYID';
    public $useTable = 'holidays';
	
}