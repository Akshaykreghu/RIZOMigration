<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class EmployeeCTC extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'EmployeeCTC';
    public $primaryKey = 'emp_ctc_upload_pkey';
    public $useTable = 'emp_ctc_upload';
}