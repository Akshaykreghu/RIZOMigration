<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class DbConfig extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */ 
    public $name = 'DbConfig';
    public $useTable = 'db_config';
}
