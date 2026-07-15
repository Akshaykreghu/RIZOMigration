<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class ProjectActivity extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'ProjectActivity';
    public $primaryKey = 'activity_pkey	';
    public $useTable = 'project_activity';
	
}