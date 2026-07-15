<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class NoticePeriod extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'NoticePeriod';
	public $primaryKey = 'notice_pkey';
    public $useTable = 'notice_period';
	
}