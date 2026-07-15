<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class Activity extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'Activity';
    public $primaryKey = 'activity_track_pkey';
    public $useTable = 'activity_track';
   
}
