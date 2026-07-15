<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SiteTransactions extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SiteTransactions';
	public $primaryKey = 'site_transactions_pkey';
    public $useTable = 'site_transactions';
	
}