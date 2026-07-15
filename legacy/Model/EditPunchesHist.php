<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EditPunchesHist extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
 
	public $primaryKey = 'device_attandance_hist_pkey';
    public $name = 'EditPunchesHist';
    public $useTable = 'device_attandance_hist';
}