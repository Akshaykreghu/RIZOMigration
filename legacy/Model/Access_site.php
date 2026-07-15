<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Access_site extends AppModel{
	/**
 * Primary key field
 *
 * @var string
 */ 
	public $name = 'Access_site';
	public $useTable = 'access_site';
	public $primaryKey = 'access_site_pkey';
	
}