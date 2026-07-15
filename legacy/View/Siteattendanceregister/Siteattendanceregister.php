<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Siteattendanceregister extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'Siteattendanceregister';
    public $primaryKey = 'site_attendance_pkey';
    public $useTable = 'site_attendance';

    public function insertUpdateAttendanceRegisterProc($outputParameter){
    	$parameter = '';
    	foreach($outputParameter as $prm)
    	{
    		$parameter .= $parameter == "" ? " '$prm' " : " , '$prm' ";
    	}
    	$query = "CALL insert_update_att_reg($parameter,@Perr_msg);";
        //echo $query;
    	$proc_result = $this->query($query);
    
    	/*$proc_data = array();
    
    	if (false !== $proc_result)
    	{
    	while ($proc_row = mysqli_fetch_array($proc_result))
    	{
    	$proc_data[] = $proc_row;
    	}
    	}
    
    	if (!empty($proc_data))
    	{
    	//do whatever with procedure data
    	//debug($proc_data);
    	echo $proc_data;
    	}*/
    	return true;
    }

    public function salaryProcessPrc($outputParameter){
    	$parameter = '';
    	foreach($outputParameter as $prm)
    	{
    		$parameter .= $parameter == "" ? " '$prm' " : " , '$prm' ";
    	}
    	$query = "CALL salary_process_prc($parameter,@Perr_msg);";
        //echo $query.PHP_EOL;
    	$proc_result = $this->query($query);
    	return true;
    }
}
