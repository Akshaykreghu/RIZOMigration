Approval<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class SiteMasterApprovalDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SiteMasterApprovalDetails';
	public $primaryKey = 'site_master_approval_details_pkey	';
    public $useTable = 'site_master_approval_details';
	
}