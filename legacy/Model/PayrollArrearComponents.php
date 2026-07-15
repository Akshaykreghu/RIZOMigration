<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class PayrollArrearComponents  extends AppModel {
    /**
     * Primary key field
     *
     * @var string
     */
    public $name = 'PayrollArrearComponents';
    public $primaryKey = 'payroll_arrear_master_pkey';
    public $useTable = 'payroll_arrear_comp_master';

    public function taxSalaryProcessPrc($outputParameter){
    	$parameter = '';
    	foreach($outputParameter as $prm)
    	{
    		$parameter .= $parameter == "" ? " '$prm' " : " , '$prm' ";
    	}
    	$query = "CALL tax_salary_process_prc($parameter,@Perr_msg);";
        //echo $query.PHP_EOL;
    	$proc_result = $this->query($query);
    	return true;
    }
}