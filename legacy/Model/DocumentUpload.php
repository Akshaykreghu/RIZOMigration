<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class DocumentUpload extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'DocumentUpload';
    public $useTable = 'document_upload';
    public $order = "DocumentUpload.creation_date DESC";
}
