<?php
App::uses('AppModel', 'Model');
/**
 * Option Items Values Model
 *
 */
class OptionItemsValues extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'OptionItemsValues';
    public $useTable = 'option_items_values';	
	public $primaryKey = 'option_items_values_pkey';
}