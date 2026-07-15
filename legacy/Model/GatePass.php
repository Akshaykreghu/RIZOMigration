<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class GatePass extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'GatePass';
    public $useTable = 'gate_pass';
    public $order = "GatePass.creation_date DESC";
}
