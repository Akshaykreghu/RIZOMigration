<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeMenu extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmployeeMenu';
    public $useTable = 'emp_menu';	
	public $primaryKey = 'menu_id';
}