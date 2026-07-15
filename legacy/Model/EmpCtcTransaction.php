<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmpCtcTransaction extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmpCtcTransaction';
	public $primaryKey = 'emp_ctc_transaction';
    public $useTable = 'emp_ctc_transaction';
	
}
