<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class IssueReport extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'IssueReport';
	public $primaryKey  = 'mob_report_pkey';
     public $useTable =  "report_a_problem1";
}