<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmpDetailedAttendanceUpload extends AppModel {
    public $name = 'EmpDetailedAttendanceUpload';
	public $primaryKey = 'emp_detailed_attendance_pkey';
    public $useTable = 'emp_detailed_attendance_uploads';
	//public $virtualFields = array('emp_name' => 'CONCAT(EmployeeDetails.first_name, " ", EmployeeDetails.last_name)');

   
}