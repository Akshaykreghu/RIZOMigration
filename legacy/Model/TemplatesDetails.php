<?php
App::uses('AppModel', 'Model');
/**
 * Doc Template  Model
 *
 */
class TemplatesDetails extends AppModel
{
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'TemplatesDetails';
    public $useTable = 'templates_details';
    public $primaryKey = 'id';
}
