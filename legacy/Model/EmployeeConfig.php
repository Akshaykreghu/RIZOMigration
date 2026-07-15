<?php
App::uses('AppModel', 'Model');
class EmployeeConfig extends AppModel {

    public $name = 'EmployeeConfig';
	public $primaryKey = 'id';
    public $useTable = 'emp_config';
	
}