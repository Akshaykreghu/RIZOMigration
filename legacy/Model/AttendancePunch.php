<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class AttendancePunch extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'AttendancePunch';
	public $primaryKey  = 'attendance_punch_pkey';
     public $useTable =  "attendance_punch";
}