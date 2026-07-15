<?php
App::uses('AppModel', 'Model');
/**
 * Doc Template  Model
 *
 */
class DocTemplate extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'DocTemplate';
    public $useTable = 'doc_template';	
	public $primaryKey = 'template_pkey';
}