<?php
App::uses('AppModel', 'Model');
/**
 * Emp Details Model
 *
 */
class SelfReviewDetails extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'SelfReviewDetails';
	public $primaryKey = 'self_review_details_pkey';
    public $useTable = 'self_review_details';
	
}
