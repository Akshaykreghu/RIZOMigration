<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Advance extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name ='Advance';
    public $primaryKey ='advance_pkey';
    public $useTable ='advance_expense';
}