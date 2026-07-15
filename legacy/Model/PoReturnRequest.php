<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class PoReturnRequest extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'PoReturnRequest';
    public $useTable = 'po_return_request';
    public $primaryKey = 'po_pkey';
}
