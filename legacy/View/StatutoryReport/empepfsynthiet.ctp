<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" id="printableContent" >
        <!--<h3 align="center" >PF STATEMENT FOR THE MONTH OF </h3>-->
        <div class="row">
            <div class="col-md-12">
                 <div style="width: 100%; height: 10px; border: 0px solid black;"></div>

                                <div style="width: 100%; text-align:center; border: 0px solid black; justify-content: center;">
                                    <table >

                                       
                                                <tr class="no-border-print">

                                                <th  class="no-border-print" style="text-align:right;padding-left:1px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name'];?></th>
                                                <!--<th style="color: white;"><?php //echo $arr_comp_contact_info['CompanyContactInfo']['business_name'];?></th>-->
                                                <!--<th style="color: white;"><?php //echo $arr_comp_contact_info['CompanyContactInfo']['business_name'];?></th>-->
                                                <!--<th style="color: white;"><?php //echo $arr_comp_contact_info['CompanyContactInfo']['business_name'];?></th>-->
                                                <!--<th style="color: white;"><?php //echo $arr_comp_contact_info['CompanyContactInfo']['business_name'];?></th>-->
                                                <th class="no-border-print" style="text-align:right;padding-left:780px;">Report Date:<?php echo $date_time;?></th>

                                            </tr>

                                            
                                <tr class="no-border-print"><th class="no-border-print" colspan="12" style="text-align:center;font-size: 15px;font-weight: bold;padding-top: 10px;padding-left:100px;">PF STATEMENT FOR THE MONTH OF <?php echo $from; ?></th></tr> 
                 <tr class="no-border-print"><th class="no-border-print" colspan="12" style="text-align:center;font-size: 15px;font-weight: bold;padding-top: 10px;padding-left:100px;">(Report Run by  <?php echo $user_id ?> at <?php echo $downtime;?>)</th></tr>
                                      
                             <!--<tr><td colspan="7" style="font-size:15px;text-align: left;font-weight: bold;">No data available under the selected criteria.</td></tr>-->
                                  
                                    </table></div>

 <?php $allEmpty = true;
//debug($arr_salary_for_template);
foreach ($arr_salary_for_template as $subArray) {
    if (!empty($subArray)) {
        $allEmpty = false;
        break;
    }
} if ($allEmpty) { ?>
                <div style="font-size: 16px;text-align:left; background-color:">
                     No data available under the selected criteria.</div>
                <?php } else { 
                
                
                $i = 0;
                foreach ($arr_salary_for_template as $value) {
                    if (count($value) !== 0) {
                       // if(!empty($value['0']['0']['SALARY'])){
                        $i += 1;
                        ?>

                        <fieldset> 


                            <legend> <?php
                        echo isset($value['0']['employee_info']['branch']) ?  $value['0']['employee_info']['branch'] : '';
                        echo ' ';
                        ?> 
                            </legend>

                        </fieldset>

                        <br>
                        <fieldset >


                            <table class="table table-bordered " style="overflow-y:initial" >
                                <thead>
                                    <!--<tr>-->
<!--edited by sinsiya on 12-06-2024changed the colspan-->
<!--                                    <th colspan="7">Employee Details</th>
                                    <th colspan="4">EPF Details</th>
                                    <th colspan="1">EPF - Employee</th>
                                    <th colspan="5">EPF - Employer</th>

                                    </tr>-->
                                    <tr>

                                    <th>Sl No</th>
                                    <th>PF NO</th>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Days Present</th>
                                    <th>NCP Days</th>
                                    <th>PF Salary</th>
                                    <th>Employee PF</th>
                                    <th>Employee VPF</th>
                                    <th>Employer PF</th>
                                    <th>Employer Pension</th>
                                    <th>Total PF</th>
                                    <!--edited by sinsiya on 12-06-2024-->
                                    <!--<th>NCP Days</th>--> 
                                    <!--<th>Gross Salary</th>-->
                                    <!--<th>UAN Number</th>-->
                                    <!--<th>EPF</th>-->
                                    <!--<th>EPF Salary</th>-->
                                    <!--<th>Excluded Salary</th>-->
                                  
                                    <!--<th>0.5%</th>-->
                                    <!--<th>0.5%</th>-->

                                    </tr>
                                </thead>

                                <tbody>
                                        <?php $dataFound = false; $arr_data = $value; ?>
                                        <?php if (count($arr_data) >= 0) {
                                            $i = 0;
                                            $sum = 0;
                                            $grandtot = 0;
                                            $pf_salary = 0;
                                            $split1= 0;
                                            $split2 = 0;
                                            $split3 = 0;
                                           // $split4 = 0;
                                            $split5=0;
                                            ?>
                                            <?php foreach ($arr_data as $val) { 
                                               
                                                $dayscount=$val['attendance_register']['leave_total']+$val['attendance_register']['weekoff_total']+$val['attendance_register']['holiday_total']+$val['attendance_register']['presant_total'];
                                                $ncp=$val['payroll_master']['calander_days']-$dayscount;
                                                 $dayspresent=$val['payroll_master']['calander_days']-$ncp ;
                                                ?>
                                            <?php if($val['0']['EPF'] != 0){
                                                 $dataFound = true; 
                                            ?>
                                            <tr>
                                                <?php $i = $i + 1;
                                                //$sum += $val['0']['balance_qty']; ?>
                                                <td><?php echo $i;
//                                                    $PO_Status = $val['po']['grn_status'];
//                                                    $string_po = '';
//                                                    switch ($PO_Status){
//                                                        case "0": $string_po = "GRN Received";
//                                                            break;
//                                                        case "1": $string_po = "PO Ordered";
//                                                            break;
//                                                        case "2": $string_po = "Finalised";
//                                                            break;
//                                                        default : $string_po = "PO";
//                                                            break;
//                                                    }
                                                ?></td>
                                            <td><?php echo $val['emp_details']['company_pf']; ?></td>
                                              <td><?php echo htmlspecialchars($val['employee_info']['employee_id']); ?></td>
                                            <td><?php echo $val['employee_info']['EmpName']; ?><?php echo isset($val['emp_details']['status']) && $val['emp_details']['status']=="2" ? '(Resigned)' : ''; ?></td>
                                           
                                            <td><?php echo $dayspresent; ?></td>
                                            <td><?php echo $ncp; ?></td>
                                            <?php if($month >= '2020-05' && $month <= '2020-07'){ 
                                             $sal = round($val['0']['EPF'] * 100 / 10, 0); 
                                             } else { 
                                             $sal = round($val['0']['EPF'] * 100 / 12, 0);
                                             } 
                                             $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 0) : round($sal * 12 / 100, 0);
                                                $epf = $pf;
                                              $sal1 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                                   if ($sal1 != '') {
                                                       $expressionWithoutPortion = str_replace('* .12', '', $sal1);
                                                       eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                                        $sal1 = floor($epf_earnings);
                                                    }
                                                  if ($epf == 1800) { $sal1= 15000;}
                                                  ?> 
                                            <td><?php echo $sal1; ?></td>
                                           <?php //if($month >= '2020-05' && $month <= '2020-07'){ ?>
                                            <!--<td><?php //echo round($val['0']['EPF'] * 100 / 10, 0); $sal = round($val['0']['EPF'] * 100 / 10, 0); ?></td>-->
                                            <?php //} else { ?>
                                            <!--<td><?php //echo floor($val['0']['EPF'] * 100 / 12); $sal = floor($val['0']['EPF'] * 100 / 12); ?></td>-->

                                            <?php //} 
                                              
                                                
                                                $pf_salary += $sal1;
                                               // $split1 += round($sal * 8.33 / 100, 0);
                                                
                                               if($month >= '2020-05' && $month <= '2020-07'){ 
                                               
                                                    //$split2 += round($sal * 1.67 / 100, 0);
                                                     //$epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                                 // $split2+= $epfnew;
                                                    $epf=round($sal * 10 / 100, 0); 
                                                   // if($epf>1800){
                                                      $split5+=0;
                                                    // }else{
                                                      $split3 += round($sal * 10 / 100, 0);
                                                    // }
                                              } else { 
                                               
                                                 // $split2 += round($sal * 3.67 / 100, 0);
                                                  // $epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                                 // $split2+= $epfnew;
                                                  $epf=round($sal * 12 / 100, 0); 
                                                 // if($epf>1800){
                                                      $split5+= 0;
                                                  //else{
                                                      $split3 += round($sal * 12 / 100, 0);
                                                 // }
                                              }
                                               // $split4 += round($sal * .5 / 100, 2);
                                            //   $pf=round($sal * 10 / 100, 2);
                                                  //  $epf=round($sal * 1.67 / 100, 2);
                                                  //   $tot=$pf+$epf+round($sal * 8.33 / 100, 2);
                                            ?>

                                              <?php if($month >= '2020-05' && $month <= '2020-07'){ ?>
                                            <td><?php $epf=round($sal * 10 / 100, 2); echo round($sal * 10 / 100, 0); ?></td>
                                            <?php } else { ?>
                                            <td><?php $epf=round($sal * 12 / 100, 2); echo round($sal * 12 / 100, 0); ?></td>
                                            <?php } ?>
                                           <?php if($month >= '2020-05' && $month <= '2020-07'){ ?>
                                            <td><?php  echo '0'; ?></td>
                                            <?php } else { ?>
                                            <td><?php echo '0';?></td>
                                            <?php } ?>
                                            
                                            <td><?php $eps_status = isset($val['emp_details']['eps'])? $val['emp_details']['eps']:'Y';
                                            if($sal>15000) {
                                                if($eps_status != 'N'){ 
                                                $emppension= round(15000 * 8.33 / 100, 0);
                                                
                                                }else{$emppension=0;}
                                              $epfnew = $val['0']['EPF']- $emppension;
                                            }else{
                                                if($eps_status != 'N'){ $emppension=round($sal * 8.33 / 100, 0);}else{$emppension=0;} 
                                                $epfnew = $val['0']['EPF']- $emppension;} $split2+= $epfnew;  echo $epfnew ; ?></td>
                                       
                                               <td><?php echo $emppension; $split1+=$emppension;?></td>
                                                   
                                          
                                            
                                             <td><?php  if ($month >= '2020-05' && $month <= '2020-07') { 
                                    $pfnew = round($sal * 10 / 100, 0);
                                   // $epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                   // $employer_pension=round($sal * 8.33 / 100, 0);
                                    $tot = $pfnew + $epfnew ;
                                      echo round($tot);
                                   //debug($tot);exit;
                                } else {
                                    $pfnew = round($sal * 12 / 100, 0);
                                   // $epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                   //  $employer_pension=round($sal * 8.33 / 100, 0);
                                    $tot = $pfnew + $epfnew;
                                      echo round($tot);
                                 
                                    
                                }
                                $grandtot += $tot;
                              
                                             ?></td>
                                            <!--edited by sinsiya on 12-06-2024-->
                                           
                                            </tr>





                                            <?php }
                                                                                            
                                                 } if (!$dataFound) { // Check the flag at the end
        echo '<tr><th colspan="11">No Data Found</th></tr>';
    }
if($pf_salary!=0){ ?>
                                            <tr>
                                                <th colspan="6">Total</th>
                                                
                                                <th><?php echo $pf_salary; ?></th>
                                               
                                                <th><?php echo $split3; ?></th>
                                                <th><?php echo $split5;?></th>
                                                <th><?php echo $split2; ?></th>
                                                <th><?php echo $split1; ?></th>
                                                <th><?php echo round($grandtot); ?></th>
                                                
                                            </tr>
                                                <?php } }// }?>


                                </tbody>
                            </table>

                        </fieldset>
                        <br>


        <?php }
                } ?> <!-- /.box-body -->

            </div>
        </div>  
        <!--div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  
        </div-->
        <!---<div class="row">
              <div class="form-group">
                  <div class="col-md-12" align="right">
                      <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                      <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','excel');"><i class="icon-file"></i>Download As Excel</a>
                  </div>
              </div>
          </div> -->
    </div>

<?php }} else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';  ?>
  <style type="text/css">
    body {
        line-height: 2em;
    }
    .page-container {
        width: 80%;
        height:90%;
        padding: 8px;
        margin: auto;
        border: #000000 solid thin;
    }
    .block-container {
        width: 100%;
    }
    .sub-head {
        border-bottom: #000000 solid thin;
    }
    .row {
        height: 20px;
    }
    .col-md-4 {
        width: 33.33%;
        float: left;
    }
    table {
       
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
        border: #000000 solid thin;
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        font-size: 9px;
      
    }
</style>
<?php
echo $this->element('reportadminheader', array(
    'title' => 'PF STATEMENT FOR THE MONTH OF ' . $from . '<br> (Report Run by ' . $user_id . ' at ' . $downtime . ')'
));
 $date_time = htmlspecialchars($date_time);

echo "<div style='font-size: 13px;text-align:right;'>Report Date: $date_time</div>";
?>




    <?php 
    $allEmpty = true;
    foreach ($arr_salary_for_template as $subArray) {
        if (!empty($subArray)) {
            $allEmpty = false;
            break;
        }
    }

    if ($allEmpty) { ?>
        <div style="font-size: 16px;text-align:left;">No data available under the selected criteria.</div>
    <?php } else { 
        $i = 0;
       
        foreach ($arr_salary_for_template as $value) {
            if (count($value) !== 0 && !empty($value[0][0]['SALARY'])) {
                $i += 1;
                ?>
        
                <fieldset style="border: none;">
                    <legend style="border: none;">
                      
                    </legend>
                </fieldset>

        <!--<br>-->
                <fieldset style="border:none;">
                    <legend style="border: none;">
                        <?php 
 echo htmlspecialchars(isset($value[0]['employee_info']['branch']) ? $value[0]['employee_info']['branch'] : ''); ?>
                    </legend>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>PF NO</th>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Days Present</th>
                                <th>NCP Days</th>
                                <th>PF Salary</th>
                                <th>Employee PF</th>
                                <th>Employee VPF</th>
                                <th>Employer PF</th>
                                <th>Employer Pension</th>
                                <th>Total PF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $arr_data = $value;
                             $dataFound = false;
                            if (count($arr_data) >= 0) {
                                $i = 0;
                                $sum = 0;
                                $grandtot = 0;
                                $pf_salary = 0;
                                $split1= 0;
                                $split2 = 0;
                                $split3 = 0;
                                $split5 = 0;
                                foreach ($arr_data as $val) { 
                                   // $dayspresent = $val['payroll_master']['calander_days'] - $val['payroll_master']['loss_of_pay'];
                                    $dayscount=$val['attendance_register']['leave_total']+$val['attendance_register']['weekoff_total']+$val['attendance_register']['holiday_total']+$val['attendance_register']['presant_total'];
                                    $ncp=$val['payroll_master']['calander_days']-$dayscount;
                                    $dayspresent=$val['payroll_master']['calander_days']-$ncp ;
                                    if ($val[0]['EPF'] != 0){
                                      $dataFound = true;
                                    $i = $i + 1;
                                    ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo htmlspecialchars($val['emp_details']['company_pf']); ?></td>
                                        <td><?php echo htmlspecialchars($val['employee_info']['employee_id']); ?></td>
                                        <td><?php echo htmlspecialchars($val['employee_info']['EmpName']); ?><?php echo isset($val['emp_details']['status']) && $val['emp_details']['status'] == "2" ? '(Resigned)' : ''; ?></td>
                                        <td><?php echo $dayspresent; ?></td>
                                        <td><?php echo $ncp; ?></td>
                                        <?php if($month >= '2020-05' && $month <= '2020-07'){ 
                                             $sal = round($val['0']['EPF'] * 100 / 10, 0); 
                                             } else { 
                                             $sal = round($val['0']['EPF'] * 100 / 12, 0);
                                             } 
                                             $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 0) : round($sal * 12 / 100, 0);
                                                $epf = $pf;
                                              $sal1 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                                   if ($sal1 != '') {
                                                       $expressionWithoutPortion = str_replace('* .12', '', $sal1);
                                                       eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                                        $sal1 = floor($epf_earnings);
                                                    }
                                                  if ($epf == 1800) { $sal1= 15000;}
                                                  ?> 
                                            <td><?php echo $sal1; ?></td>
                                            <?php
                                            $pf_salary += $sal1;
                                           // $split1 += round($sal * 8.33 / 100, 0);
                                            if ($month >= '2020-05' && $month <= '2020-07') { 
                                               // $split2 += round($sal * 1.67 / 100, 0);
                                                $epf = round($sal * 10 / 100, 0); 
                                              //  if ($epf > 1800) {
                                                    $split5 += 0;
                                                //} else {
                                                    $split3 += round($sal * 10 / 100, 0);
                                               // }
                                            } else { 
                                                //$split2 += round($sal * 3.67 / 100, 0);
                                                $epf = round($sal * 12 / 100, 0); 
                                               // if ($epf > 1800) {
                                                    $split5 += 0;
                                               // } else {
                                                    $split3 += round($sal * 12 / 100, 0);
                                               // }
                                            }
                                        ?>
                                      <?php if($month >= '2020-05' && $month <= '2020-07'){ ?>
                                            <td><?php $epf=round($sal * 10 / 100, 2); echo round($sal * 10 / 100, 0); ?></td>
                                            <?php } else { ?>
                                            <td><?php $epf=round($sal * 12 / 100, 2); echo round($sal * 12 / 100, 0); ?></td>
                                            <?php } ?>
                                           <?php if($month >= '2020-05' && $month <= '2020-07'){ ?>
                                            <td><?php  echo '0'; ?></td>
                                            <?php } else { ?>
                                            <td><?php echo '0';?></td>
                                            <?php } ?>
                                      <td><?php $eps_status = isset($val['emp_details']['eps'])? $val['emp_details']['eps']:'Y';
                                            if($sal>15000) {
                                                if($eps_status != 'N'){ 
                                                $emppension= round(15000 * 8.33 / 100, 0);
                                                
                                                }else{$emppension=0;}
                                              $epfnew = $val['0']['EPF']- $emppension;
                                            }else{
                                                if($eps_status != 'N'){ $emppension=round($sal * 8.33 / 100, 0);}else{$emppension=0;} 
                                                $epfnew = $val['0']['EPF']- $emppension;} $split2+= $epfnew;  echo $epfnew ; ?></td>
                                       
                                               <td><?php echo $emppension; $split1+=$emppension;?></td>
                                        <td><?php  if ($month >= '2020-05' && $month <= '2020-07') { 
                                    $pfnew = round($sal * 10 / 100, 0);
                                   // $epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                   // $employer_pension=round($sal * 8.33 / 100, 0);
                                    $tot = $pfnew + $epfnew;
                                      echo round($tot);
                                   //debug($tot);exit;
                                } else {
                                    $pfnew = round($sal * 12 / 100, 0);
                                   // $epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                    // $employer_pension=round($sal * 8.33 / 100, 0);
                                    $tot = $pfnew + $epfnew;
                                      echo round($tot);
                                 
                                    
                                }
                                $grandtot += $tot;
                              
                                             ?></td>
                                    </tr>
                                    <?php } }if (!$dataFound) { // Check the flag at the end
        echo '<tr><th colspan="11">No Data Found</th></tr>';
    } if($pf_salary!=0){?>
                                <tr>
                                    <th colspan="6">Total</th>
                                    <th><?php echo $pf_salary; ?></th>
                                    <th><?php echo $split3; ?></th>
                                    <th><?php echo $split5;?></th>
                                    <th><?php echo $split2; ?></th>
                                    <th><?php echo $split1; ?></th>
                                    <th><?php echo round($grandtot); ?></th>
                                </tr>
    <?php } } ?>
                        </tbody>
                    </table>
                </fieldset>
                <?php 
            }
        } 
    } ?> <!-- /.box-body -->
<!--</div>-->


<?php } ?>
