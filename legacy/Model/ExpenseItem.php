<?php
App::uses('AppModel', 'Model');

/**
 * 
 */
class ExpenseItem extends AppModel
{
 public $name ='ExpenseItem';
 public $useTable = 'expense_item';
 public $primaryKey = 'expense_item_pkey';
}
?>