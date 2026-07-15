<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SiteHistory extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SiteHistory';
	public $primaryKey = 'site_history_pkey';
    public $useTable = 'site_history';
	
}