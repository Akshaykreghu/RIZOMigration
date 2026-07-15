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
    public $name = 'ShiftException';
    public $primaryKey = 'id';
    public $useTable = 'shift_exceptions';
}
