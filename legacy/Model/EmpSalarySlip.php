<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmpSalarySlip extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmpSalarySlip';
    public $primaryKey = 'emp_salary_slip_pkey';
    public $useTable = 'emp_salary_slip';
}