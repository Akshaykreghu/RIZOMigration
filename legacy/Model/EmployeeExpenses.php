<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeExpenses extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name ='EmployeeExpenses';
    public $primaryKey ='emp_expenses_pkey';
    public $useTable ='emp_expense';
}