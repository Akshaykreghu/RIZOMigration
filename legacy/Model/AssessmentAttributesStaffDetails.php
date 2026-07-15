<?php
App::uses('AppModel', 'Model');
/**
 * Emp Details Model
 *
 */
class AssessmentAttributesStaffDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'AssessmentAttributesStaffDetails';
	public $primaryKey = 'attr_staff_details_pkey';
    public $useTable = 'assessment_attributes_staff_details';
	
}
