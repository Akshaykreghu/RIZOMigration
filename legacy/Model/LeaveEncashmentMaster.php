<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class LeaveEncashmentMaster extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'LeaveEncashmentMaster';
    public $useTable = 'leave_encashment_master';
    public $primaryKey = 'leave_encashment_master_pkey';
}
