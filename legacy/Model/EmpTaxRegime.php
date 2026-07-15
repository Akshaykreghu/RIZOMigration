<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmpTaxRegime extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmpTaxRegime';
    public $primaryKey = 'emp_tax_regime_id';
    public $useTable = 'emp_tax_regime';
	
}