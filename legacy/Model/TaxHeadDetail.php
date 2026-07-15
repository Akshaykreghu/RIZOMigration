<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class TaxHeadDetail extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'TaxHeadDetail';
    public $useTable = 'tax_heads_details';
	public $primaryKey = 'tax_heads_details_pkey';
}
