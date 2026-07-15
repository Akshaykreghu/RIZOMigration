<?php
App::uses('AppModel', 'Model');

/**
 * 
 */
class ExpenseHead extends AppModel
{
 public $name ='ExpenseHead';
 public $useTable = 'expense_heads';
 public $primaryKey = 'expense_heads_pkey';
}
?>