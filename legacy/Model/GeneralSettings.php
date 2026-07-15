<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class GeneralSettings extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'GeneralSettings';
    public $primaryKey = 'settings_pkey';
    public $useTable = 'genaral_setings';

}
