<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class CompanyInfo extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'CompanyInfo';
    public $useTable = 'db_config';
}
