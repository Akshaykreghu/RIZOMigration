<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class DayTimeProcedures extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'DayTimeProcedures';
	public $primaryKey = 'day_time_seq';
    public $useTable = 'working_day_time_procedures';
	
}


