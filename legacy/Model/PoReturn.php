<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class PoReturn extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'PoReturn';
    public $useTable = 'return_gr_items';
    public $primaryKey = 'rtn_pkey';
}
