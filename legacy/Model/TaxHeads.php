<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class TaxHeads extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'TaxHeads';
    public $useTable = 'tax_heads';
	public $primaryKey = 'tax_heads_pkey';
}
