<?php
App::uses('AppModel', 'Model');
/**
 * Efsr Equipment Master  Model
 *
 */
class EfsrEquipmentsMaster extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EfsrEquipmentsMaster';
	public $primaryKey = 'efsr_equipments_master_pkey';
    public $useTable = 'efsr_equipments_master';
	
}
