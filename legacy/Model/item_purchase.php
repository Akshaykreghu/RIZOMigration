<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class item_purchase extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'item_purchase';
	public $primaryKey = 'item_pkey';
    public $useTable = 'item_purchase';
	
}