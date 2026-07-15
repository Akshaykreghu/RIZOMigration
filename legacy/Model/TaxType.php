<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class TaxType extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'TaxType';
    public $useTable = 'tax_type';
	public $primaryKey = 'tax_type_pkey';
}
