<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class OutPass extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'OutPass';
    public $useTable = 'out_pass';
    public $order = "OutPass.out_pass_pkey ASC";
}
