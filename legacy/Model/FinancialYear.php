<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class FinancialYear extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'FinancialYear';
	public $primaryKey = 'Fin_year_seq';
    public $useTable = 'fin_year';
	
}