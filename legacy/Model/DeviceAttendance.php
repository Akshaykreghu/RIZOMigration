<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class DeviceAttendance extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
 
    public $name = 'DeviceAttendance';
	public $primaryKey = 'device_attandance_seq';
    public $useTable = 'device_attandance';
   
}
