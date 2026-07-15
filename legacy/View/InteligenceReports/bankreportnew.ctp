<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
<style>

    #report_table1 td, th{
        border-style: solid;
        border-color: #d4d4de;
        overflow-x: auto;
    }

    #report_table2 td, th{
        border-style: solid;
        border-color: #d4d4de;
        overflow-x: auto;
    }
   

    .modal-content {
        width: 125%   !important;
    }
</style>
<?php if ($mode == '') { 
?>
<div class="modal-body" style="overflow-y:auto; padding-left:3%; padding-right:3%; padding-bottom:3%;">

    <h2 style="font-weight: bold;text-align: center;font-size:20px;"> <?php echo $date_time;?> </h2>
 
     <?php $i = 0;
foreach ($arr_salary_for_template as $value) {
                        foreach ($value as $valuees) {
                             if (!empty($valuees)) {
                                 $i++;
                             }
                             }
                             }
                             if($i == '0'){ ?>
                    <div style="font-size: 16px;text-align:left; background-color:;">
                  No data available under the selected criteria</div>
                             <?php } else {?>
    <div class="col-md-12">
        <?php if($criteria != 'Banks') {
                 foreach($arr_salary_for_template as $value){ ?>
       
            <?php if (count($value['leavepolicyname']) <= 0) { ?>
            <?php } else { 
                $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
                if($criteria != 'LeavePolicyGroup' || $bk != '' ){
                ?>
                <legend style="border: 0;font-size: 18px;">
              <?php if($cr=='EmployeeDetails'){ echo $value['leavepolicyname']['0']['info']['EmpName']; echo (isset($value['leavepolicyname']['0']['EmployeeDetails']['status'])) && $value['leavepolicyname']['0']['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';}
               elseif ($cr == 'Units'){ echo $value['leavepolicyname']['0']['info']['branch']; }
                elseif ($cr == 'Departments'){ echo $value['leavepolicyname']['0']['info']['department']; } else { 
            $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
            

            echo ($bk != '')?$bk:'N/A'; } ?></legend>
 <div class="">
        <div style="overflow-x: auto;">
            <table class="table table-bordered"  id="report_table1" style="overflow-x: auto;">
                <thead>
                    <tr>
                        
                        <th>DEBIT ACCOUNT NUMBER</th>
                        <th>AMOUNT</th>
                        <th>IFSC CODE</th>
                        <th>NEFT/RTGS</th>
                        <th>ACCOUNT NUMBER</th>
                        <th>NAME</th>
                        <th>BRANCH</th>
                        <th>DEBIT ACCOUNT NUMBER</th>
                        <th>SMS</th>
                        <th>PHONE NO</th>
                    </tr>
                </thead>
                    <?php 
                    $i=1;
$total = 0;
                    foreach($value['leavepolicyname'] as $val){  
                
                                $bank_name = '';
                                $branch_name = '';
                                $ifsc_code = '';
                                $acc_number = '';
                                $bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
                                if($bank !=''){
                                list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
                                }
                               
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                       
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    }
                    ?>
                       <tbody>
                   
                        <?php if(true){ ?>
                        <?php $table_count = 0;?>
                         <tr>
                         <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                        <?php 
                        $dd_amt=round($val['payroll_master']['net_salary'] + $settle);
                        ?>
                        <?php 
                        $netamt=$dd_amt;
$total = $total +$netamt;
                        ?>
                        <td><?php echo $netamt; ?></td>
                        <td><?php echo $val['EmployeeDetails']['ifsc_code'];?></td>
                        <td><?php echo $payment; ?></td>
                        <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                        <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                        <td><?php  $bank_branch = $val['EmployeeDetails']['branch_name']; echo $bank_branch;?></td>
                        <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                        <td><?php $sms="YES"; echo $sms;?></td>
                        <td><?php echo $val['EmployeeDetails']['mobile_no'];?></td>
                    </tr>
                    <?php  $i++;
                } else{?>
                    <!-- <tr><td colspan="11" style="text-align:center;">
                    No data available under the selected criteria
</td></tr> -->
                    <?php }   }?>
<tr><td><b>Total</b></td><td><b><?php echo $total; ?></b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                </tbody>
                <?php $table_count++;
                ?>
            </table>
        </div>
 </div>
            <?php  } ?>
            <?php  } ?>
       
            <?php }?> 
                <?php if($table_count == 0){?>
                    <div style="font-size: 16px;text-align:left; background-color:;">
                  No data available under the selected criteria</div>
                    <?php }?>
            <?php } else {
                 $i = 0;
                 foreach($arr_salary_for_template as $value){ 
                    ?>
        
            <?php if (count($value['leavepolicyname']) > 0) { ?>
            
            <legend style="border: 0;font-size: 18px;">
                <?php $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
            
            echo ($bk != '')?$bk:'N/A';  ?><?php ?></legend>
<div class="">
            <div style="overflow-x: auto;">
            <table class="table table-bordered" id="report_table2" style="overflow-x: auto;">
                <thead>
                    <tr>
                        <th>DEBIT ACCOUNT NUMBER</th>
                        <th>AMOUNT</th>
                        <th>IFSC CODE</th>
                        <th>NEFT/RTGS</th>
                        <th>ACCOUNT NUMBER</th>
                        <th>NAME</th>
                        <th>BRANCH</th>
                        <th>DEBIT ACCOUNT NUMBER</th>
                        <th>SMS</th>
                        <th>PHONE NO</th>
                   </tr>
                </thead>
                <tbody>
                    <?php 
                    $i=1;
                    $total = 0;
                    foreach($value['leavepolicyname'] as $val){  
                                $bank_name = '';
                                $ifsc_code = '';
                                $acc_number = '';
                                                    $branch_name = '';
                                $bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
                                if($bank !=''){
                                list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
                                }
                             
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                       
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    } ?>
                    <?php if(true){ ?>
                    <tr>
                         <td ><?php  echo $val['EmployeeDetails']['account_no'];?></td>
                         <td><?php $dd_amt=round($val['payroll_master']['net_salary'] + $settle);$netamt=$dd_amt;echo $netamt; ?></td>
                         <td><?php echo $val['EmployeeDetails']['ifsc_code'];?></td>
                         <td><?php echo $payment; ?></td>
                         <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                         <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                         <td><?php echo $val['EmployeeDetails']['branch_name'];?></td>
                         <td ><?php echo $val['EmployeeDetails']['account_no'];?></td>
                         <td ><?php $sms= "YES";echo $sms;?></td>
                         <td><?php echo $val['EmployeeDetails']['mobile_no'];?></td>
  
                        <?php 
                        $dd_amt=round($val['payroll_master']['net_salary'] + $settle); ?>
                        <?php $netamt=$dd_amt;  ?>
                        
                    </tr>
                    <?php  $i++;
$total = $total +$netamt;} else{?>
                    <tr><td colspan="5" style="text-align:center;">
                    No data available under the selected criteria
</td></tr>
                    <?php }   }?>
<tr><td><b>Total</b></td><td><b><?php echo $total; ?></b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                </tbody>
            </table>
            </div>
</div>
            <?php } ?>
            <?php } ?>
                
            <?php  }  ?>
            <?php if($i == 0){?>
                <div style="font-size: 16px;text-align:left; background-color:;">
                  No data available under the selected criteria</div>
                <?php }?>
            </div>     <?php }  ?>
</div>
                             <?php  }else{ ?>
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
echo $this->element('reportadminheader',array(
'title'=>'Salary Bank Transfer '));
?>


    <h2 style="font-weight: bold;text-align: center;"> <?php echo $date_time;?> </h2>
    
     <?php $i = 0; 
foreach ($arr_salary_for_template as $value) {
                        foreach ($value as $valuees) {
                             if (!empty($valuees)) {
                                 $i++;
                             }
                             }
                             }
                             if($i == '0'){ ?>
                    <div style="font-size: 16px;text-align:left; ">
                  No data available under the selected criteria</div>
                             <?php } else {?>
   
        <?php if($criteria != 'Banks') {
                 foreach($arr_salary_for_template as $value){ ?>
       <div class="box-body">
            <?php if (count($value['leavepolicyname']) <= 0) { ?>
            <?php } else { 
                $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
                if($criteria != 'LeavePolicyGroup' || $bk != '' ){
                ?>
                <h2>
              <?php if($cr=='EmployeeDetails'){ 
echo $value['leavepolicyname']['0']['info']['EmpName']; 
echo (isset($value['leavepolicyname']['0']['EmployeeDetails']['status'])) && $value['leavepolicyname']['0']['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';}
               elseif ($cr == 'Units'){ echo $value['leavepolicyname']['0']['info']['branch']; }
                elseif ($cr == 'Departments'){ echo $value['leavepolicyname']['0']['info']['department']; } else { 
            $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
            

            echo ($bk != '')?$bk:'N/A'; } ?></h2>
        
            <table class="table table-bordered"  id="report_table1" style="overflow-x: auto;">
                <thead>
                    <tr>
                        
                        <th>DEBIT <br>ACCOUNT NUMBER</th>
                        <th>AMOUNT</th>
                        <th>IFSC CODE</th>
                        <th>NEFT/RTGS</th>
                        <th>ACCOUNT NUMBER</th>
                        <th>NAME</th>
                        <th>BRANCH</th>
                        <th>DEBIT <br>ACCOUNT NUMBER</th>
                        <th>SMS</th>
                        <th>PHONE NO</th>
                    </tr>
                </thead>
                    <?php 
                    $i=1;
$total = 0;
                    foreach($value['leavepolicyname'] as $val){  
                
                                $bank_name = '';
                                $branch_name = '';
                                $ifsc_code = '';
                                $acc_number = '';
                                $bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
                                if($bank !=''){
                                list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
                                }
                               
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                       
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    }
                    ?>
                       <tbody>
                   
                        <?php if(true){ ?>
                        <?php $table_count = 0;?>
                         <tr>
                         <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                        <?php 
                        $dd_amt=round($val['payroll_master']['net_salary'] + $settle);
                        ?>
                        <?php 
                        $netamt=$dd_amt;
                        ?>
                        <td><?php echo $netamt; ?></td>
                        <td><?php echo $val['EmployeeDetails']['ifsc_code'];?></td>
                        <td><?php echo $payment; ?></td>
                        <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                        <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                           <td><?php  $bank_branch = $val['EmployeeDetails']['branch_name']; echo $bank_branch;?></td>
                          <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                          <td><?php $sms="YES"; echo $sms;?></td>
                         <td><?php echo $val['EmployeeDetails']['mobile_no'];?></td>
                    </tr>
                    <?php  $i++;
                    $total = $total +$netamt;
                } else{?>
                   
                    <?php }   }?>
<tr><td><b>Total</b></td><td><b><?php echo $total; ?></b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                </tbody>
                <?php $table_count++;
                ?>
            </table>
        
            <?php  } ?>
            <?php  } ?>
      </div>
            <?php }?> 
                <?php if($table_count == 0){?>
                    <div style="font-size: 16px;text-align:left;">
                  No data available under the selected criteria</div>
                    <?php }?>
            <?php } else {
                 $i = 0;
                 foreach($arr_salary_for_template as $value){ 
                    ?>
         <div class="box-body">
            <?php if (count($value['leavepolicyname']) > 0) { ?>
        
            
            <h2>
                <?php $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:''; 
            
            echo ($bk != '')?$bk:'N/A';  ?><?php ?></h2>
          
            <table class="table table-bordered" id="report_table2" style="overflow-x: auto;">
                <thead>
                    <tr>
                        <th>DEBIT<br> ACCOUNT NUMBER</th>
                        <th>AMOUNT</th>
                        <th>IFSC CODE</th>
                        <th>NEFT/RTGS</th>
                        <th>ACCOUNT NUMBER</th>
                        <th>NAME</th>
                        <th>BRANCH</th>
                        <th>DEBIT <br>ACCOUNT NUMBER</th>
                        <th>SMS</th>
                        <th>PHONE NO</th>
                   </tr>
                </thead>
                <tbody>
                    <?php 
                    $i=1;
$total = 0;
                    foreach($value['leavepolicyname'] as $val){  
                                $bank_name = '';
                                $ifsc_code = '';
                                $acc_number = '';
                                                    $branch_name = '';
                                $bank = isset($val['payroll_master']['bank_details'])?$val['payroll_master']['bank_details']:'';
                                if($bank !=''){
                                list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
                                }
                             
                    $settle = 0;
                    foreach($arr_settle as $values){
                        $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;
                       
                        if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                            $settle = $values[0][0]['sum(salary_amount)'];
                        }
                        
                    } ?>
                    <?php if(true){ ?>
                    <tr>
                         <td ><?php  echo $val['EmployeeDetails']['account_no'];?></td>
                         <td><?php $dd_amt=round($val['payroll_master']['net_salary'] + $settle);$netamt=$dd_amt;echo $netamt; ?></td>
                         <td><?php echo $val['EmployeeDetails']['ifsc_code'];?></td>
                         <td><?php echo $payment; ?></td>
                         <td><?php echo $val['EmployeeDetails']['account_no'];?></td>
                         <td><?php echo $val['info']['EmpName'];echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                         <td><?php echo $val['EmployeeDetails']['branch_name'];?></td>
                         <td ><?php echo $val['EmployeeDetails']['account_no'];?></td>
                         <td ><?php $sms= "YES";echo $sms;?></td>
                         <td><?php echo $val['EmployeeDetails']['mobile_no'];?></td>
  
                        <?php 
                        $dd_amt=round($val['payroll_master']['net_salary'] + $settle); ?>
                        <?php $netamt=$dd_amt;  ?>
                        
                    </tr>
                    <?php  $i++;
$total = $total +$netamt;
} else{?>
                    <tr><td colspan="5" style="text-align:center;">
                    No data available under the selected criteria
</td></tr>
                    <?php }   }?>
<tr><td><b>Total</b></td><td><b><?php echo $total; ?></b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                </tbody>
            </table>
            <?php } ?></div>
            <?php } ?>

            <?php  }  ?>
            <?php if($i == 0){?>
                <div style="font-size: 16px;text-align:left; background-color:;">
                  No data available under the selected criteria</div>
                <?php }?>
              <?php }  ?>

                             <?php } ?>