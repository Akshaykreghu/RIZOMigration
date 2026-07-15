<?php
App::uses('AppModel', 'Model');
/**
 * Emp Details Model
 *
 */
class AssessmentAttributesStaffItem extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'AssessmentAttributesStaffItem';
	public $primaryKey = 'aast_pkey';
    public $useTable = 'assessment_attributes_staff_items';
	
}
