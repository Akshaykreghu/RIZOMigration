<?php
App::uses('AppModel', 'Model');
/**
 * Survey Type Model
 *
 */
class SurveyType extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SurveyType';
    public $useTable = 'survey_type';	
	public $primaryKey = 'type_pkey';
}