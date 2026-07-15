<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SiteAttendance extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SiteAttendance';
	public $primaryKey = 'site_attendance_pkey';
    public $useTable = 'site_attendance';
	
}