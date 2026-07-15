<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class Device extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'Device';
    public $primaryKey = 'DeviceId';
    public $useTable = 'devices';
    public $useDbConfig = 'controldb';

}
