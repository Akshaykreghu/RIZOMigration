<?php
App::uses('AppModel', 'Model');
/**
 * Survey Category Model
 *
 */
class SurveyCategory extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SurveyCategory';
    public $useTable = 'survey_category';	
	public $primaryKey = 'category_pkey';
}