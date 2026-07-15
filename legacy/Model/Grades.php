<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Grades extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Grades';
    public $useTable = 'grade';
}
