<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class LeaveRequests extends AppModel {
/**
 * Primary key field
 *
 * @var string
 */ 
    public $name = 'LeaveRequests';
	public $primaryKey = 'LEAVEENTRYID';
    public $useTable = 'leaveentries';
	
	public function leaveTransactionPrc($outputParameter){
        $parameter = '';
	    //unset($outputParameter['LEAVESTATUS']);
        foreach($outputParameter as $prm)
        {
            $parameter .= $parameter == "" ? " $prm " : " , $prm ";
        } 
        $query = "CALL leave_transaction_prc($parameter,@Perror_message);";
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
}