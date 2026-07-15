<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Status extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Status';
    public $useTable = 'workflow_status';
}
