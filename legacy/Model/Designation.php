<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Designation extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Designation';
    public $useTable = 'designation';
    public $order = "Designation.desig_name ASC";
}
