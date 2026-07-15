<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class MobileUserTracking extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 

    public $name = 'MobileUserTracking';
	public $primaryKey  = 'mob_location_pkey';
     public $useTable =  "mob_user_tracking";
}