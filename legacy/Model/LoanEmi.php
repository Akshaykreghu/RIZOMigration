<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class LoanEmi extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'LoanEmi';
	public $primaryKey = 'emi_upload_pkey';
    public $useTable = 'emi_upload';
	
}