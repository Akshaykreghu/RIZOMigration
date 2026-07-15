<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Assets extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'Assets';
	public $primaryKey  = 'asset_pkey';
     public $useTable =  "asset_management";
}