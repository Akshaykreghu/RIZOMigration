<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class ActivityProjects extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'ActivityProjects';
    public $primaryKey = 'activity_projects_pkey';
    public $useTable = 'activity_projects';
   
}
