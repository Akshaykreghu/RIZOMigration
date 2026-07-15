<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class ExceptionRule extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'ExceptionRule';
    public $primaryKey = 'exception_id';
    public $useTable = 'exception_rule';
}