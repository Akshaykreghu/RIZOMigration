<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class Payrollarrearmaster extends AppModel
{
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'Payrollarrearmaster';
    public $primaryKey = 'payroll_arrear_master_pkey';
    public $useTable = 'payroll_arrear_master';

    public function taxArrearProcessPrc($outputParameter)
    {
        $parameter = '';
        foreach ($outputParameter as $prm) {
            $parameter .= $parameter == "" ? " '$prm' " : " , '$prm' ";
        }
        $query = "CALL tax_arrear_process_prc($parameter,@Perr_msg);";
        //echo $query.PHP_EOL;

        $proc_result = $this->query($query);
        return true;
    }

    public function taxArrearProcessOldPrc($outputParameter)
    {
        $parameter = '';
        foreach ($outputParameter as $prm) {
            $parameter .= $parameter == "" ? " '$prm' " : " , '$prm' ";
        }
        $query = "CALL tax_salary_process_old_prc($parameter,@Perr_msg);";
        //echo $query.PHP_EOL;

        $proc_result = $this->query($query);
        return true;
    }
}
