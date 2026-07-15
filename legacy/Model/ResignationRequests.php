<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class ResignationRequests extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'ResignationRequests';
    public $primaryKey = 'Resignation_pkey';
    public $useTable = 'resignation_requests';
}
