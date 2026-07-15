Approval<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SiteMasterApproval extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SiteMasterApproval';
	public $primaryKey = 'site_master_approval_pkey';
    public $useTable = 'site_master_approval';
	
}