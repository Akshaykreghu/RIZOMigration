<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class GrItemDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'GrItemDetails';
    public $useTable = 'gr_item_details';
    public $primaryKey = 'gr_item_pkey';
}
