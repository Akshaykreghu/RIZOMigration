<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Devicelog extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'DeviceLog';
	public $primaryKey  = 'device_attandance_seq';
     public $useTable =  "device_attandance";
}