<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeLeaveUpload extends AppModel {
    public $name = 'EmployeeLeaveUpload';
	public $primaryKey = 'emp_leave_upload_pkey';
    public $useTable = 'emp_leave_upload';
	//public $virtualFields = array('emp_name' => 'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name)');

   
}
