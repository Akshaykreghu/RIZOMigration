<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Section extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Section';
    public $useTable = 'section';
}
