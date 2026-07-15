<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmpDocument extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EmpDocument';
    public $useTable = 'emp_documents';
}
