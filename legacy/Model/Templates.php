<?php
App::uses('AppModel', 'Model');
/**
 * Doc Template  Model
 *
 */
class Templates extends AppModel
{
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'Templates';
    public $useTable = 'templates';
    public $primaryKey = 'id';
}
