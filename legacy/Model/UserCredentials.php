<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class UserCredentials extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'UserCredentials';
    public $primaryKey = 'user_pkey';
    public $useTable = 'user_credentials';
    
    public function linkempDeviceanddatabase($outputParameter){
        $parameter = '';
        foreach($outputParameter as $prm)
        {
            $parameter .= $parameter == "" ? " $prm " : " , $prm ";
        } 
        $query = "CALL Linkemp_deviceanddatabase($parameter,@Perror_message);";
        $proc_result = $this->query($query);
        
        return true;
    }
}
