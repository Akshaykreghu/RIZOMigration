<?php

App::uses('AppModel', 'Model');

class AttendanceRegisterReport extends AppModel {

    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'AttendanceRegisterReport';
    public $primaryKey = 'registerid';
    public $useTable = 'attendance_register_rep';

    public function insertUpdateAttendanceRegisterForReportProc($outputParameter) {
        $parameter = '';
        foreach ($outputParameter as $prm) {
            $parameter .= $parameter == "" ? " '$prm' " : " , '$prm' ";
        }
        $query = "CALL insert_update_att_reg_rep($parameter,@Perr_msg);";
        $proc_result = $this->query($query);
        return true;
    }

}
