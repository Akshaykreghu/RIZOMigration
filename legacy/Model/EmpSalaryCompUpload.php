<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmpSalaryCompUpload extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
 
	public $primaryKey = 'emp_salcomp_upload_pkey';
    public $name = 'EmpSalaryCompUpload';
    public $useTable = 'emp_salcomp_upload';
}