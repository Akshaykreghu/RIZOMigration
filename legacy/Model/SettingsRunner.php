<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class SettingsRunner extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'SettingsRunner';
    public $primaryKey = 'setting_runner_pkey';
    public $useTable = 'settings_runner';

}
