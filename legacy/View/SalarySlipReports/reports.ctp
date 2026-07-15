<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
    .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
    border: 1px solid #070707;
}
</style>
<div class="modal-body" style="overflow-y: auto;">
    
    <?php  if (isset($arr_salary_for_template['0']['summary']) && count($arr_salary_for_template['0']['summary']) <= 0) { ?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        Payroll Not Processed For this Month </div>
    <?php } else {
    ?>
        <legend align="center" >Salary Slip - <?php echo "$mname1-".$y1; ?> </legend>
        <?php if(isset($msgs)) { echo $msgs;  } ?>
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    $i = 0;
                    foreach ($arr_salary_for_template as $value) {
                        if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                            $i += 1;
                            ?>
                            <div class="box-body">
                                <fieldset> 
                                    
       <div class="row" align="center" style="border-style: solid; border-width: 1px;margin-left: 30px; margin-right: 30px; margin-bottom: -21px; padding-top: 5px; padding-bottom: 6px;">

           <b style="padding-left: 15px;"><?php
        echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
        echo ' ';
          echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
        echo ' ';
        echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
        ?></b> - <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?>  -  <?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : ''; ?></th>
            
            

<!--                                              <th>Leave days</th>
            <th>Holidays</th>-->

</div>
<div class="row " style="padding-top: 10px; margin-top: 21px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
    <div class="col-md-3 ">
        Employee ID 
    </div>
     <div class="col-md-3 ">
        <span style="text-align: right ; word-wrap: break-word; ">:  <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?>  </span><span style="float: right ;"></span>
    </div>
     <div class="col-md-3">
         Date of Joining 
     </div>
      <div class="col-md-3 ">
          <span style="text-align: right ;  border-bottom: 0px solid white ; ">:  <?php
        
       echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
        ?>  </span><span style="float: right ;"></span>
      </div>
</div>
                                    <div class="row " style="padding-top: 10px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
    <div class="col-md-3">
      Department   
    </div> 
    <div class="col-md-3">
       <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : ''; ?>  </span><span style="float: right ;"></span> 
    </div>
    <div class="col-md-3">
         Gender  
         </div>
    <div class="col-md-3">
           <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['ed']['classification']) ? strtoupper($value['summary']['0']['ed']['classification']) : ''; ?>   </span><span style="float: right ;"></span>
     
        </div>
   
</div>
   <div class="row " style="padding-top: 10px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
    <div class="col-md-3">
         <span style="text-align:  left ; ">Leave Days  </span>
         </div>
    <div class="col-md-3">
                   <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['days_leave']) ? $value['empdet']['0']['payroll_master']['days_leave'] : ''; ?>   </span><span style="float: right ;"></span>

    </div>
  
     <div class="col-md-3">
           <span style="text-align:  left ; ">Present Days </span>
            </div>
     <div class="col-md-3">
           <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['days_presant']) ? $value['empdet']['0']['payroll_master']['days_presant'] : ''; ?>   </span><span style="float: right ;"></span>
      
    </div>
    </div>
    <div class="row " style="padding-top: 10px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
    <div class="col-md-3">
         <span style="text-align:  left ; ">Lop Days   </span>
         </div>
    <div class="col-md-3">
                   <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '0'; ?>   </span><span style="float: right ;"></span>

    </div>
  
     <div class="col-md-3">
           <span style="text-align:  left ; ">No. of Week Off   </span>
            </div>
     <div class="col-md-3">
           <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['week_off_days']) ? $value['empdet']['0']['payroll_master']['week_off_days'] : '0'; ?>   </span><span style="float: right ;"></span>
      
    </div>
    </div>
<div class="row " style="padding-top: 10px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
    <div class="col-md-3">
         <span style="text-align:  left ; ">No. of Holiday   </span>
         </div>
    <div class="col-md-3">
                   <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: 
                       <?php echo isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : '0'; ?>   </span><span style="float: right ;"></span>

    </div>
    <div class="col-md-3">
        <span style="text-align:  left ; ">PF account No   </span>
         </div>
     <div class="col-md-3">    
       <span style="text-align: right ; ">: <?php
     
        echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
        ?>  </span><span style="float: right ;"></span>
             </div>
     
</div>
  <div class="row " style="padding-top: 10px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
     <div class="col-md-3">
        <span style="text-align:  left ; ">ESI No   </span>
           </div>
     <div class="col-md-3">  
       :<?php echo isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?>   
       
    </div>
     <div class="col-md-3">
            <span style="text-align:  left ; ">UAN No   </span>
             </div>
     <div class="col-md-3">
           <span style="text-align: left ; ">: <?php
        
        echo wordwrap(isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : '',20,"<br>\n",TRUE);
        ?>  </span><span style="float: left ;"></span>
     </div>
</div>
<div class="row " style="padding-top: 10px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
     <div class="col-md-3"> 

 <span style="text-align:  left ; ">Bank Name   </span>
    </div>  
 <div class="col-md-3"> 
<span style="text-align: right ; ">: <?php
        
        echo isset($value['summary']['0']['ed']['bank_name']) ? $value['summary']['0']['ed']['bank_name'] : '';
        ?> </span><span style="float: right ;">
            </div>
     <div class="col-md-3">
            <span style="text-align:  left ; ">Account Number   </span>
             </div>
     <div class="col-md-3">
           <span style="text-align: left ; ">: <?php
        
        echo wordwrap(isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '',20,"<br>\n",TRUE);
        ?>  </span><span style="float: left ;"></span>
     </div>
</div>
<div class="row " style="padding-top: 10px; margin-left: 30px; margin-right: 30px; border-right: 1px solid black; border-left: 1px solid black;">
     <div class="col-md-3"> 
            <span style="text-align:  left ; ">IFSC Code   </span>
     </div>
     <div class="col-md-3">
            <span style="text-align: right ; ">: <?php
        
        echo isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
        ?>  </span><span style="float: right ;"></span>
        </div>
     <div class="col-md-3">
          <span style="text-align:  left ; ">Branch   </span>
          </div>
     <div class="col-md-3">
            : <?php echo isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : ''; ?>   </span><span style="float: right ;"></span></th>
            </div>
</div>
                                        
                                        </fieldset>

                                <br>
                                <fieldset>


                                  <table class="table table-bordered" align="center" style="width: 790px; margin-top: -20px; border: 1px solid #000; ">
    <tbody>
        <tr style="background: #cccccc ;width : 120% ;" >

                                    <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                    <th style="width:40%" >Earnings</th>
                                    <th style="width:10%" >Amount</th>
                                    <th style="width:41%" >Deductions</th>

                                                                      <!--<th>Leave days</th>-->
                                    <th style="width:10%" >Amount</th>

                                </tr>
                    <?php $arr_data = $value['summary'];
                    $arr_withoutComponents = $value['withoutcomponent'];
                    ?>
                    <?php
                    if (count($arr_data) >= 0) {
                        $countss = count($arr_data);
                        if(count($arr_data) < count($arr_withoutComponents)){
                            $countss = count($arr_withoutComponents);
                        }
                        $sum = 0;
                        $tot = 0;
                        $dd = 0;
                        $net = 0;
                        ?>
                        <?php for ($i = 0; $i < $countss; $i++) {
                            ?>
                            <tr> <?php
                                $sum += isset($arr_data[$i]['ectc']['salary_amount'])?$arr_data[$i]['ectc']['salary_amount']:0;
                                    if(isset($arr_withoutComponents[$i]['ectc']))
                                    $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount'])?$arr_withoutComponents[$i]['ectc']['salary_amount']:0;
                                ?>
                                <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc'])?$arr_data[$i]['ectc']['salary_head_item_desc']:''; ?></td>
                                <!--<td><?php echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; ?></td>-->
                                <td><?php echo isset($arr_data[$i]['ectc']['salary_amount'])?abs(round($arr_data[$i]['ectc']['salary_amount'], 2)):''; ?></td>
                                <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                <!--<td><?php echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); ?></td>-->
                                <td><?php echo round(isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs($arr_withoutComponents[$i]['ectc']['salary_amount']) : '', 2); ?></td>

                            </tr>

                <?php } ?>
                        <tr style="background: #cccccc ;" >
                            <th style="text-align :center ; ">Total Earnings</th><th><?php echo abs($sum); ?></th><th>Total Deductions </th><th><?php echo abs($dd); ?></th>
                        </tr>
                        <tr style="background: #cccccc ;" >
                            <th style="text-align :center ; " colspan="3">Net Pay</th><th><?php echo $sum+$dd; ?></th>
                        </tr>
                        <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                <?php if (count($arr_withoutComponents) > 0) { ?>



                        <?php } ?>
                    <?php } else {
                        ?>
                        <tr>
                            <td colspan="4">No Components found under this data</td>
                        </tr>  
            <?php } ?>   

                </tbody>
</table>

                                </fieldset>
                                <br>
                            </div>

                        <?php
                        }
                    }
                    ?> <!-- /.box-body -->
                </div>
            </div>
        </div>  
        <?php  if (isset($arr_salary_for_template['0']['summary']) && count($arr_salary_for_template['0']['summary']) <= 0) { ?>
        <div class="row">
            <div class="form-group">
                <div class="col-md-12" align="right">
                    <a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                    <!--a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip','excel');"><i class="icon-file"></i>Download As Excel</a-->
                </div>
            </div>
        </div>
        <?php } ?>
        <?php } 
    ?>
    </div>
<script>
   
function downloadReport(){
         var Date = $('#reportfrom').val();
         var url = livesite+'SalarySlipReports/SalarySlipdownload/'+Date;
                               $(location).attr('href',url);  
                                      
}
</script>