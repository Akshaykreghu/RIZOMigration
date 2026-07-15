<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class AssetsName extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'AssetsName';
	public $primaryKey  = 'asset_pkey';
     public $useTable =  "asset_management";
}