<?php
App::uses('AppModel', 'Model');

class ExpenseTypePaymentDetails extends AppModel
{
	public $name = 'ExpenseTypePaymentDetails';
        public $useTable = 'emp_expense_payment';	
	public $primaryKey = 'payment_pkey';
}
