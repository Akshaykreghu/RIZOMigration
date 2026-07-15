<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmpArrearSalarySlip extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmpArrearSalarySlip';
    public $primaryKey = 'emp_new_salary_slip_pkey';
    public $useTable = 'emp_new_salary_slip'; // Edited by Akshay on 23-5-2025
}