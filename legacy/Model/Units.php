<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Units extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Units';
    public $useTable = 'branches';
    public $order = "Units.branch_name ASC";
}
