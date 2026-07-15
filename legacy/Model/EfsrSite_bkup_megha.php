<?php
App::uses('AppModel', 'Model');
/**
 * Efsr Site Model
 *
 */
class EfsrSite extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EfsrTickets';
	public $primaryKey = 'efsr_site_pkey';
    public $useTable = 'efsr_site';
	
}
