<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class HolidayGroup extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'HolidayGroup';
	public $primaryKey = 'HOLIDAY_GROUP_ID';
    public $useTable = 'holiday_group';
	
}