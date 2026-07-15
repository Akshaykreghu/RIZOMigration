<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class CentralControl extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'login_auditor';
    public $primaryKey = 'login_audit_pkey';
    public $useTable = 'login_auditor';
    public $useDbConfig = 'controldb';
    public $tablePrefix = '';

}
