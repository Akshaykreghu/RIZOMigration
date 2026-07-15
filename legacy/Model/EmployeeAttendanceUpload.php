<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeAttendanceUpload extends AppModel {
    public $name = 'EmployeeAttendanceUpload';
	public $primaryKey = 'emp_attendance_upload_pkey';
    public $useTable = 'emp_attendance_upload';
	//public $virtualFields = array('emp_name' => 'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name)');

   
}