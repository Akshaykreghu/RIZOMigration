<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class VehicleExpenses extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name ='VehicleExpenses';
    public $primaryKey ='transportation_expense_pkey';
    public $useTable ='transportation_expense';
}