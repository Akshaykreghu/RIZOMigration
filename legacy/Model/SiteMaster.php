<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SiteMaster extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SiteMaster';
	public $primaryKey = 'site_pkey';
    public $useTable = 'site';
	
}