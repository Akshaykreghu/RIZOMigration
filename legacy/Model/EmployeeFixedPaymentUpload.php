<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeFixedPaymentUpload extends AppModel {
    public $name = 'EmployeeFixedPaymentUpload';
	public $primaryKey = 'emp_fixed_component_upload_pkey';
    public $useTable = 'emp_fixed_component_upload';
	//public $virtualFields = array('emp_name' => 'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name)');

   
}
