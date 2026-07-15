<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeLoan extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmployeeLoan';
    public $primaryKey = 'emp_loan_pkey';
    public $useTable = 'emp_loan';
}