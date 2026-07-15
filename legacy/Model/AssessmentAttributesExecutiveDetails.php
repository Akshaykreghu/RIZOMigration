<?php
App::uses('AppModel', 'Model');
/**
 * Emp Details Model
 *
 */
class AssessmentAttributesExecutiveDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'AssessmentAttributesExecutiveDetails';
	public $primaryKey = 'attr_exec_details_pkey';
    public $useTable = 'assessment_attributes_executive_details';
	
}
