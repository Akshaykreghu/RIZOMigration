<?php
App::uses('AppModel', 'Model');

class ExpenseType extends AppModel
{
	public $name = 'ExpenseType';
        public $useTable = 'expense_type';	
	public $primaryKey = 'expense_type_pkey';
}
?>