<?php
App::uses('AppModel', 'Model');
/**
 * Documents  Model
 *
 */
class Documents extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Documents';
    public $useTable = 'documents';	
	public $primaryKey = 'document_pkey';
}