<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class AssetsModel extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'AssetsModel';
	public $primaryKey  = 'asset_pkey';
     public $useTable =  "asset_management";
}