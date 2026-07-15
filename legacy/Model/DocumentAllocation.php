<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class DocumentAllocation extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'DocumentAllocation';
    public $useTable = 'document_allocation';
    public $order = "DocumentAllocation.allocated_date DESC";
}
