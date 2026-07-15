<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class GatePassItems extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'GatePassItems';
    public $useTable = 'gate_pass_items';
    public $order = "GatePassItems.gate_pass_items_pkey	ASC";
}
