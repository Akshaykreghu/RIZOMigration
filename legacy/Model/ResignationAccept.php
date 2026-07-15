<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class ResignationAccept extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'ResignationAccept';
	public $primaryKey = 'resignation_accept_pkey';
    public $useTable = 'resignation_accept';
	
}