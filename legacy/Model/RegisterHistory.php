<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class RegisterHistory extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'RegisterHistory';
    public $primaryKey = 'register_history_pkey';
    public $useTable = 'register_history';
}
