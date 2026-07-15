<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class TaxHead extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'TaxHead';
    public $useTable = 'tax_heads';
	public $primaryKey = 'tax_heads_pkey';
}
