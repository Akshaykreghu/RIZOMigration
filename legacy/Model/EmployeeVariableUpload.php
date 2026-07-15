<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeVariableUpload extends AppModel {
    public $name = 'EmployeeVariableUpload';
	public $primaryKey = 'emp_variables_upload_pkey';
    public $useTable = 'emp_variables_upload';
	//public $virtualFields = array('emp_name' => 'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name)');

   
}
