<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Menu extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Menu';
    public $useTable = 'hrm_menu';
	
	public $primaryKey = 'menu_id';
}
