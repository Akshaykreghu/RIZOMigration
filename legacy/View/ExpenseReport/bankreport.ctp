<style>
    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;
   }
    .modal-content {
        width: 125%   !important;
    }
</style>
<?php if ($mode == '') { 
?>
<div class="modal-body" style="overflow-y:auto; padding-left:3%; padding-right:3%; padding-bottom:3%;">
 <?php $i = 0;
foreach ($arr_salary_for_template as $value) {
                        foreach ($value as $valuees) {
                             if (!empty($valuees)) {
                                 $i++;
                             }
                             }
                             }
                             if($i == '0'){ ?>
                    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available</div>
                             <?php } else {?>
    <div class="row">
        <?php if($criteria != 'Banks') {
                 foreach($arr_salary_for_template as $value){ ?>
        <div class="col-md-12">
            <?php if (count($value['leavepolicyname']) <= 0) { ?>
            <?php } else { ?>
            <h3 align="center"  >Salary Bank Transfer of <?php if($cr=='EmployeeDetails'){ echo $value['leavepolicyname']['0']['info']['EmpName']; echo (isset($value['leavepolicyname']['0']['EmployeeDetails']['status'])) && $value['leavepolicyname']['0']['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';} elseif ($cr == 'Units'){ echo $value['leavepolicyname']['0']['info']['branch']; }else { 
			$bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
			
			//$bk_name = '';
			//if($bk !=''){
			//	 list($bk_name, $br_name, $ifsc,$acc) = explode(',', $bk);
			//}debug($bk_name);
			//if($bk_name == ''){
            //     $bk_name = $value['leavepolicyname']['0']['0']['BANK_NAME'];
			//}
			echo ($bk != '')?$bk:'N/A'; } ?></h3>
            <h3>Month-<?php echo $month;?></h3>
            <table class="table ">
                <tbody>
                    <tr style="background-color:#f0f0ff;">
                        <th>Sl No</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Bank Name</th>
                        <th>Branch Name</th>
                        <th>IFSC Code</th>
                        <th>Acc. Number</th>
                        <th>Net Salary</th>
 </tr>
                    <?php 
                    $i=1;
                    foreach($value['leavepolicyname'] as $val){  
				
					            $bank_name = '';
					            $branch_name = '';
					            $ifsc_code = '';
					            $acc_number = '';
								$bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
								if($bank !=''){
					            list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
					            }
								if($bank_name == ''){
                                $bank_name = $val['0']['BANK_NAME'];
								}
								if($branch_name == ''){
                                $branch_name = $val['EmployeeDetails']['branch_name'];
								}
								if($ifsc_code == ''){
                                $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
								}
								if($acc_number == ''){
                                $acc_number = $val['EmployeeDetails']['account_no'];
								}
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                       
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    }
            
                  //end
                    ?>
<!--                      // edited by megha on 25_06_19 zero valued net salary removed-->
                    <?php if($val['payroll_master']['net_salary'] > 0){ ?>
                    <tr>
                        <td ><?php echo $i;?></td>
                        <td><?php echo $val['info']['employee_id'];?></td>
                        <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                        <td><?php echo $val['info']['designation'];?></td>
                        <td><?php echo $val['info']['department'];?></td>
                        <td><?php echo $val['info']['branch'];?></td>
                        <td><?php echo $bank_name;?></td>
                        <td><?php echo $branch_name;?></td>
                        <td><?php echo $ifsc_code;?></td>
                        <td><?php echo $acc_number;?></td>
                        <!--<td><?php // echo isset($val['ot']) ? round(($val['ot'] / 60), 2) :0; ?></td>-->
                        <?php //$count_addition= count($val['Addition']['keys']); 
//                        $grss_amt=round($val['0']['payroll_master']['gross_salary']);
                        //for($m=0;$m<$count_addition;$m++)
                        //{
                        //$number=round($val['Addition']['value'][$m]);
                        //$grss_amt=$number+$grss_amt;


                        ?>

                        <?php //} ?>
                        <!--<td><?php // echo $grss_amt; ?></td>--> 
                        <?php //$count_addition= count($val['Deduction']['keys']); 
               // edited by megha on 14_11_19 settlement amount 2
                       //if($indirect == 0){
                        $dd_amt=round($val['payroll_master']['net_salary'] + $settle);
//                       }else{
//                        $dd_amt=round($val['payroll_master']['net_salary'] + $val['0']['salary'] + $settle);
//                       }
                       //end
                        //for($m=0;$m<$count_addition;$m++)
                        //{
                        //$number=round($val['Deduction']['value'][$m]);
                        //$dd_amt=$number+$dd_amt;


                        ?>


                        <?php //} 


                        $netamt=$dd_amt;
                        ?>


                        <!--<td><?php // echo $dd_amt; ?></td>-->
                        <td><?php echo $netamt; ?></td>
                    </tr>
                    <?php  $i++;} else{?>
					<tr><td colspan="11" style="text-align:center;">
        There is no data available
</td></tr>
                    <?php }   }?>

                </tbody>
            </table>

            <?php  } ?>
        </div>
            <?php } } else {
                 foreach($arr_salary_for_template as $value){ ?>
        <div class="col-md-12">
            <?php if (count($value['leavepolicyname']) > 0) { ?>
            <?php //} else { ?>
            <h3 align="center"  >Salary Statement of 
                <?php $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
			
			echo ($bk != '')?$bk:'N/A';  ?></h3>
            <h3>Month : <?php echo $month;?></h3>
            <table class="table ">
                <tbody>
                    <tr style="background-color:#f0f0ff;">
                        <th>Sl No</th>
                        <th>Beneficiary Identification</th>
                        <th>Transaction Amount</th>
                        <th>Beneficiary Bank IFSC</th>
                        <th>Beneficiary Bank A/C Number</th>
                   </tr>
                    <?php 
                    $i=1;
                    foreach($value['leavepolicyname'] as $val){  
					            $bank_name = '';
					            $ifsc_code = '';
					            $acc_number = '';
                                                    $branch_name = '';
								$bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
								if($bank !=''){
					            list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
					            }
								if($bank_name == ''){
                                $bank_name = $val['0']['BANK_NAME'];
								}
								if($branch_name == ''){
                                $branch_name = $val['EmployeeDetails']['branch_name'];
								}
								if($ifsc_code == ''){
                                $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
								}
								if($acc_number == ''){
                                $acc_number = $val['EmployeeDetails']['account_no'];
								}
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                       
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    } ?>
                    <?php if($val['payroll_master']['net_salary'] > 0){ ?>
                    <tr>
                        <td ><?php echo $i;?></td>
                        <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
  
                        <?php 
                        $dd_amt=round($val['payroll_master']['net_salary'] + $settle); ?>
                        <?php $netamt=$dd_amt;  ?>

                        <td><?php echo $netamt; ?></td>
                         <td><?php echo $ifsc_code;?></td>
                        <td><?php echo $acc_number;?></td>
                    </tr>
                    <?php  $i++;} else{?>
					<tr><td colspan="5" style="text-align:center;">
        There is no data available
</td></tr>
                    <?php }   }?>

                </tbody>
            </table>
            <?php } ?>
 </div>
            <?php  }  ?>
       
            </div>     <?php }  ?> 
    <!--div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  
    </div-->
    <!-- <div class="row">
         <div class="form-group">
             <div class="col-md-12" align="right">
                 <a href="#" class="btn btn-default" onclick="downloadReport('Grosssalary', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                 <a href="#" class="btn btn-default" onclick="downloadReport('Grosssalary', 'excel');"><i class="icon-file"></i>Download As Excel</a>
             </div>
         </div>
     </div> -->
                             <?php } ?>
</div>
                             <?php  }else{ ?>
<?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>'; ?>
<style type="text/css">
    body {
        line-height: 2em;
    }
    .block-container {
        width: 95%;
        padding: 20px;
        border: #000000 solid thin;
    }
    .sub-head {
        border-bottom: #000000 solid thin;
    }
    .row {
        height: 32px;
    }
    .col-md-4 {
        width: 33.33%;
        float: left;
    }
    table {
        border: 1px solid #f4f4f4;
        width: 80%;
        max-width: 80%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>

<?php
//echo $this->element('reportadminheader',array(
//'title'=>'Salary Statement Report'));
?>
<div class="modal-body" style="overflow-y:auto; padding-left:3%; padding-right:3%; padding-bottom:3%;">
 <?php $i = 0;
    foreach ($arr_salary_for_template as $value) {
        foreach ($value as $valuees) {
            if (!empty($valuees)) {
                $i++;
            }
        }
    }
    if($i == '0'){ ?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available</div>
    <?php } else {?>
    
        <?php if($criteria != 'Banks') {
                 foreach($arr_salary_for_template as $value){ ?>
     
            <?php if (count($value['leavepolicyname']) <= 0) { ?>
            <?php } else { ?>
            <h3 align="center"  >Salary Bank Transfer of <?php if($cr=='EmployeeDetails'){ echo $value['leavepolicyname']['0']['info']['EmpName']; echo (isset($value['leavepolicyname']['0']['EmployeeDetails']['status'])) && $value['leavepolicyname']['0']['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';} elseif ($cr == 'Units'){ echo $value['leavepolicyname']['0']['info']['branch']; }else { 
			$bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
			
			echo ($bk != '')?$bk:'N/A'; } ?></h3>
            <h3>Month : <?php echo $month;?></h3>
            <table class="table ">
                <tbody>
                    <tr style="background-color:#f0f0ff;">
                        <th>Sl No</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Bank Name</th>
                        <th>Branch Name</th>
                        <th>IFSC Code</th>
                        <th>Acc. Number</th>
                        <th>Net Salary</th>
                    </tr>
                    <?php 
                    $i=1;
                    foreach($value['leavepolicyname'] as $val){  
			$bank_name = '';
			$branch_name = '';
			$ifsc_code = '';
			$acc_number = '';
			$bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
			if($bank !=''){
			list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
			}
			if($bank_name == ''){
                        $bank_name = $val['0']['BANK_NAME'];
			}
			if($branch_name == ''){
                        $branch_name = $val['EmployeeDetails']['branch_name'];
			}
			if($ifsc_code == ''){
                        $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
			}
			if($acc_number == ''){
                        $acc_number = $val['EmployeeDetails']['account_no'];
			}
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    } ?>
                    <?php if($val['payroll_master']['net_salary'] > 0){ ?>
                    <tr>
                        <td ><?php echo $i;?></td>
                        <td><?php echo $val['info']['employee_id'];?></td>
                        <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                        <td><?php echo $val['info']['designation'];?></td>
                        <td><?php echo $val['info']['department'];?></td>
                        <td><?php echo $val['info']['branch'];?></td>
                        <td><?php echo $bank_name;?></td>
                        <td><?php echo $branch_name;?></td>
                        <td><?php echo $ifsc_code;?></td>
                        <td><?php echo $acc_number;?></td>
                        <?php  $dd_amt=round($val['payroll_master']['net_salary'] + $settle); ?>
                        <?php $netamt=$dd_amt; ?>
                        <td><?php echo $netamt; ?></td>
                    </tr>
                    <?php  $i++;} else{?>
					<tr><td colspan="11" style="text-align:center;">
        There is no data available
</td></tr>
                    <?php }   }?>

                </tbody>
            </table>

            <?php  } ?>
   
            <?php } } else {
                 foreach($arr_salary_for_template as $value){ ?>
    
            <?php if (count($value['leavepolicyname']) > 0) { ?>
            <?php //} else { ?>
            <h3 align="center"  >Salary Statement of 
                <?php $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
			
			echo ($bk != '')?$bk:'N/A';  ?></h3>
            <h3>Month-<?php echo $month;?></h3>
            <table class="table ">
                <tbody>
                    <tr style="background-color:#f0f0ff;">
                        <th>Sl No</th>
                        <th>Beneficiary Identification</th>
                        <th>Transaction Amount</th>
                        <th>Beneficiary Bank IFSC</th>
                        <th>Beneficiary Bank A/C Number</th>
                   </tr>
                    <?php 
                    $i=1;
                    foreach($value['leavepolicyname'] as $val){  
					            $bank_name = '';
					            $ifsc_code = '';
					            $acc_number = '';
                                                    $branch_name = '';
								$bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
								if($bank !=''){
					            list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
					            }
								if($bank_name == ''){
                                $bank_name = $val['0']['BANK_NAME'];
								}
								if($branch_name == ''){
                                $branch_name = $val['EmployeeDetails']['branch_name'];
								}
								if($ifsc_code == ''){
                                $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
								}
								if($acc_number == ''){
                                $acc_number = $val['EmployeeDetails']['account_no'];
								}
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                       
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    } ?>
                    <?php if($val['payroll_master']['net_salary'] > 0){ ?>
                    <tr>
                        <td ><?php echo $i;?></td>
                        <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
  
                        <?php 
                        $dd_amt=round($val['payroll_master']['net_salary'] + $settle); ?>
                        <?php $netamt=$dd_amt;  ?>

                        <td><?php echo $netamt; ?></td>
                         <td><?php echo $ifsc_code;?></td>
                        <td><?php echo $acc_number;?></td>
                    </tr>
                    <?php  $i++;} else{?>
					<tr><td colspan="5" style="text-align:center;">
        There is no data available
</td></tr>
                    <?php }   }?>

                </tbody>
            </table>
            <?php } ?>

            <?php  }  ?>
       
              <?php }  ?> 
  
                             <?php } ?>
</div>
                             <?php } ?>