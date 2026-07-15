<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Banks extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Banks';
    public $useTable = 'bank';
}
