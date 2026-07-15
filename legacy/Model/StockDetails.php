<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class StockDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'StockDetails';
    public $useTable = 'stock_details';
    public $primaryKey = 'stock_details_pkey';
}
