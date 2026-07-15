<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class MobileUserCredentials extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'MobileUserCredentials';
	public $primaryKey  = 'user_pkey';
     public $useTable =  "mob_user_credentials";
}