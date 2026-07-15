<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Equipments extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name ='Equipments';
    public $primaryKey ='efsr_equipments_master_pkey';
    public $useTable ='efsr_equipments_master';
}