<?php
App::uses('AppModel', 'Model');
/**
 * Equipment Type Model
 *
 */
class EquipmentType extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EquipmentType';
    public $useTable = 'equipment_type';	
	public $primaryKey = 'equipment_type_pkey';
}