<?php
App::uses('AppModel', 'Model');

class ExpenseTypeDetails extends AppModel
{
	public $name = 'ExpenseTypeDetails';
        public $useTable = 'emp_expense_details';	
	public $primaryKey = 'expense_details_pkey';
}
?>