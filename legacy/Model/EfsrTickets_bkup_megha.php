<?php
App::uses('AppModel', 'Model');
/**
 * Efsr Tickets Model
 *
 */
class EfsrTickets extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'EfsrTickets';
	public $primaryKey = 'efsr_tickets_pkey';
    public $useTable = 'efsr_tickets';
	
}
