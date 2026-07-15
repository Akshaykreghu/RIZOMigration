<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Departments extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Departments';
    public $useTable = 'department';
    public $order = "Departments.dept_name ASC";
}
