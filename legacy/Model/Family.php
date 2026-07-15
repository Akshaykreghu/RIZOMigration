<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppModel', 'Model');

class Family extends AppModel {
    public $name = 'Family';   
    public $primaryKey = 'emp_family_pkey';
    public $useTable = 'emp_family';    
   
}
?>