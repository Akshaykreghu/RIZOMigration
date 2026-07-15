<?php
App::uses('AppModel', 'Model');
/**
 * Option Items Model
 *
 */
class OptionItems extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'OptionItems';
    public $useTable = 'option_items';	
	public $primaryKey = 'option_items_pkey';
}