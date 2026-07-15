<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EditPunches extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
 
	public $primaryKey = 'device_attandance_seq';
    public $name = 'EditPunches';
    public $useTable = 'device_attandance';
}
