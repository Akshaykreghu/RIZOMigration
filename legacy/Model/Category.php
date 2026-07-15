<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Category extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Category';
    public $useTable = 'category';
}
