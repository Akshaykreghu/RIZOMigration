<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeProfessionalDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeProfessionalDetails';
	public $primaryKey = 'emp_proff_pkey';
    public $useTable = 'emp_proff';
	
	/*public $belongsTo = array(
   		'EmployeeDetails' => array(
   			'className' => 'EmployeeDetails',
            'foreignKey' => 'emp_fkey'
    	)
 	);*/
}