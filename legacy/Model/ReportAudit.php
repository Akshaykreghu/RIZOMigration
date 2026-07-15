<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class ReportAudit extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'ReportAudit';
    public $primaryKey = 'id';
    public $useTable = 'report_audit';
	
	
}