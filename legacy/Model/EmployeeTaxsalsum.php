<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
App::uses('AppModel', 'Model');
class EmployeeTaxsalsum extends AppModel {

    public $name = 'EmployeeTaxsalsum';
	public $primaryKey = 'emp_tax_sal_trans_sum_pkey';
    public $useTable = 'emp_tax_sal_trans_sum';
	
}