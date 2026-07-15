<?php //debug($arr_tds_report);   ?>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="border:1px solid #3C8DBC">
        <legend>Tax TDS Reports</legend>
        <div class="modal-content">
            <?php if (count($arr_tds_report) <= 0) { ?>

                There is no data available with respect to your report</div>

        <?php } else { ?>
            <?php foreach ($arr_tds_report as $value) { ?>
        <div>
            <hr>
                <div class="row">
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">First Name:<?php echo $value['EmployeeDetails']['first_name']; ?></div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Last Name:<?php echo $value['EmployeeDetails']['last_name']; ?></div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Company ID:<?php echo $value['EmployeePro']['emp_company_id']; ?></div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Employee type:<?php echo $value['EmployeePro']['emp_type']; ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Date Of Join:<?php echo $value['EmployeePro']['joining_date']; ?></div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Pan NO:<?php echo $value['EmployeeDetails']['pan_no']; ?></div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Bank Name:<?php echo $value['EmployeeDetails']['bank_name']; ?></div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Branch Name:<?php echo $value['EmployeeDetails']['branch_name']; ?></div>
                </div>
        <hr>
                <div class="col-md-12"> 
                    <div class="col-md-6"> 
                        <div class="box-header">
                            <table class="table table-bordered" style="border:1px solid #3C8DBC">
                                <thead>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th colspan="2" style="background:#9bd7d5;">
                                            <h3>Tax FY- 2016</h3> 
                                            <h3>Summary Calculation</h3>
                                        </th>
                                    </tr>
                                </tbody><tbody>
                                    <!--tr><th>Taxable salary  </th><td><?php echo $taxcomponents['EmployeeTaxsalsum']['taxable_salary']; ?></td></tr-->
                                    <tr><th>Taxable Income from salary </th><td><?php echo $value['EmployeeTaxsalsum']['taxable_income']; ?></td></tr>
                                    <tr><th>Taxable Income from Other Sources </th><td><?php echo $value['EmployeeTaxsalsum']['other_income']; ?></td></tr>
                                    <tr><th>Investments &amp; Other Deductions</th><td><?php echo $value['EmployeeTaxsalsum']['declared_deduction']; ?></td></tr>
                                </tbody>
<!--                                <tbody><tr><th colspan="2">Total Taxable Income   <h5>(Taxable Income from salary + Taxable Income from Other Sources - Deductions)</h5><h2 class="pull-right">360668</h2></th></tr>-->
                                </tbody><tbody>
                                    <tr><th>Rs.2,50,000 - Rs.5,00,000 + </th><td><?php echo $value['EmployeeTaxsalsum']['first_portion']; ?></td></tr>
                                    <tr><th>Rs.5,00,000 - Rs.10,00,000 + </th><td><?php echo $value['EmployeeTaxsalsum']['second_portion']; ?></td></tr>
                                    <tr><th>Rs.10,00,000 and beyond + </th><td><?php echo $value['EmployeeTaxsalsum']['third_portion']; ?></td>
                                    <tr><th>Yearly Tax  </th><td><?php echo $value['EmployeeTaxsalsum']['tax_yearly']; ?></td></tr>
                                    <tr><th>Current Monthly Tax  </th><td><?php echo $value['EmployeeTaxsalsum']['tax_monthly_proj']; ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>  
                    <div class="col-md-6"> 
                        <div class="box-header">
                            <table class="table table-bordered" style="border:1px solid #3C8DBC">
                                <thead>
                                </thead>
                                <tbody><tr><th colspan="2" style="background:#9bd7d5;"><h3>House Rent Allowance</h3></th></tr>
                                </tbody><tbody>
                                    <tr><th>40/50 % of Basic</th><td><?php echo $value['EmployeeTaxsalsum']['hra1']; ?></td></tr>
                                    <tr><th>Actual HRA Received</th><td><?php echo $value['EmployeeTaxsalsum']['hra2']; ?></td></tr>

                                    <tr><th>Rent Paid - (10 % of Basic)</th><td><?php echo $value['EmployeeTaxsalsum']['hra3']; ?></td></tr>
                                </tbody>
                                <tbody>
                                    <tr><th>HRA Exemption</th><th><?php echo $value['EmployeeTaxsalsum']['hra3']; ?></th></tr>
                                </tbody>
                                <tbody><tr><th colspan="2" style="background:#9bd7d5;"><h3>Salary </h3> <h3>Details</h3></th></tr>
                                </tbody><tbody>
                                 <tr><th>Actual salary Received</th><td></td></tr>
                                    <tr><th> Projected Salary</th><td><?php echo $value['EmployeeTaxsalsum']['availed_salary']; ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>




            <?php }?>
                     
            
            
    <table class="table table-bordered">
                            <thead>
                              <tr>
                                
                             <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                  <th>Salary</th>
                                  <th>Item</th>
								  <!--th>Actual Salary</th>
								  <th>Projected Salary</th-->
                                 <th>Availed Salary</th>
                                  
                                <th>Upper Limit</th>
                                  <th>Taxable Salary</th>
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                                <?php if(count($taxcomponents)>=0){ $sum = 0; $total =0; $actual = 0; $project = 0;?>
                                    <?php foreach($taxcomponents as $val){ 
                                        ?>
                                            <tr> <?php $sum = $sum + $val['EmpTaxSalTrans']['taxable_salary']; $total = $total +  $val['EmpTaxSalTrans']['availed_salary']; //$actual = $actual +  $val['EmpTaxSalTrans']['actual_salary_recd']; $project = $project + $val['EmpTaxSalTrans']['projected_salary']; ?>
                                             <td><?php echo $val['TSC']['tax_salary_components_name']; ?></td>
                                            <td><?php echo $val['SHI']['item']; ?></td>
											<!--td><?php echo $val['EmpTaxSalTrans']['actual_salary_recd']; ?></td>
											<td><?php echo $val['EmpTaxSalTrans']['projected_salary']; ?></td-->
                                            <td><?php echo $val['EmpTaxSalTrans']['availed_salary']; ?></td>
                                             <td><?php echo $val['EmpTaxSalTrans']['upper_limit']; ?></td>
                                             <td><?php echo $val['EmpTaxSalTrans']['taxable_salary']; ?></td>
                                            </tr>
                                      
                                    <?php } ?>
                                          <tr>
                                            <th>Gross Total</th><th></th><!--th><?php echo $actual ; ?></th><th><?php echo $project ; ?></th--><th><?php echo $total ; ?></th><th></th><th><?php echo $sum ; ?></th>
                                        </tr>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this data</td>
                                        </tr>  
                                <?php } ?>
                                     
                              
                            </tbody>
                        </table> 
            
            
               <div class="col-md-12"> 
                            <?php 
                            if(isset($taxcomponents))
                            {

                            ?>
                            <?php $tax1 =  count($taxdates) + 1; ?>
                            <?php $tax2 =  count($months); ?>
                            <div class="box-header">
                                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                                    <thead>
                                        <tr><th colspan="<?php echo $tax1; ?>" style="background:#3C8DBC;color: antiquewhite;"><h2></h2> <h3>Salary for the Year</h3></th></tr>
                                        <tr><th>Months</th> <?php
                                            if(isset($taxdates))
                                            {
                                            $label = array();
                                            foreach($taxdates as $value1)
                                            {
                                            $timestamp1 = strtotime($value1['months']);
                                            $label[] = date('F',$timestamp1);
                                            ?>
                                            <th><?php echo date('F-Y',$timestamp1); ?></th>

                                            <?php
                                            }
                                            }
                                            ?>
                                    </thead>

                                    <tbody>
                                    <th>Tax</th>
                                    <?php
                                    if(isset($taxdates))
                                    {
                                    $taxlabel = array();
                                    foreach($taxdates as $value1)
                                    {
                                    
                                    ?>
                                    <?php if(isset($value1['tax']['0']['0']['tdsdeducted'])) { ?><td style="font-weight: bold;"><?php echo $value1['tax']['0']['0']['tdsdeducted'] ;$taxlabel[] = $value1['tax']['0']['0']['tdsdeducted']; } else { ?><td style='color:red; font-weight:bold;'><?php echo isset($taxcomponents['0']['EmployeeTaxsalsum']['tax_monthly_proj'])? $taxcomponents['0']['EmployeeTaxsalsum']['tax_monthly_proj']: '0' ;$taxlabel[] = $taxcomponents['0']['EmployeeTaxsalsum']['tax_monthly_proj']; } ?></td>
                                    
                                    <?php
                                    
                                    }
                                    }
                                    ?>
                                    


                                    <tr>
                                        <th>Salary</th>

                                        <?php
                                        if(isset($taxdates))
                                        {
                                        $salabel = array();
                                        foreach($taxdates as $value1)
                                        {

                                        ?>
                                        <?php if(isset($value1['salary']['0']['0']['tdsdeducted'])) { ?><td style="font-weight: bold;"><?php echo $value1['salary']['0']['0']['tdsdeducted'];$salabel[] = $value1['salary']['0']['0']['tdsdeducted']; } else { ?><td style='color:red; font-weight:bold;'><?php echo $salary['0']['0']['amount'];$salabel[] = $salary['0']['0']['amount']; } ?></td>
                                        
                                        <?php
                                        
                                        }
                                        }
                                        ?>
                                    </tr>
                                    <tr>
                                        <td colspan="<?php echo $tax1; ?>"><div class="pull-right"><span class="label" style='background: black;'>Actual</span>&nbsp;&nbsp;<span class="label label-danger">Projected</span></div></td> 
                                    </tr>

                                    </tbody>


                                </table>
                                
                            </div>
                            <?php
                            }
                            ?>
                        </div> 
            
            
            
            
            
            
        </
        div>        
            
            
    <?php    }
        ?>

    </div> 

<!--    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('TaxTDS', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('TaxTDS', 'excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>-->
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';      ?>
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
        .no-border{
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 0px solid #B2B2B2;
        }
    </style>
    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Tax TDS Reports'));
    ?>
   
            <?php if (count($arr_tds_report) <= 0) { ?>

                There is no data available with respect to your report

        <?php } else { ?>
            <?php foreach ($arr_tds_report as $value) { ?>
       
            
                <table class="no-border">
                    <tr  class="no-border"><td  class="no-border"  ><b>Name:</b><?php echo $value['EmployeeDetails']['first_name']; ?>
                   <?php echo $value['EmployeeDetails']['last_name']; ?></td>  
                   <td  class="no-border"><b>Date Of Join:</b><?php echo $value['EmployeePro']['joining_date']; ?></td>   
                   <td  class="no-border"><b>Employee Type:</b><?php echo $value['EmployeePro']['emp_type']; ?></td></tr>
                </table>
                 <table class="no-border">
                    <tr  class="no-border"><td  class="no-border"><b>Company ID:</b><?php echo $value['EmployeePro']['emp_company_id']; ?></td>
                    <td  class="no-border"><b>Pan NO:</b><?php echo $value['EmployeeDetails']['pan_no']; ?></td>
                   <td  class="no-border"><b>Bank Name:</b><?php echo $value['EmployeeDetails']['bank_name']; ?></td>
                 <td  class="no-border"><b>Branch Name:</b><?php echo $value['EmployeeDetails']['branch_name']; ?></td>
                 </tr></table>
       <hr>
               
        <table class="no-border"><tr class="no-border">
                
         <td class="no-border"></td>        <td class="no-border"></td>     
                <td class="no-border">
 <table class="table table-bordered" style="border:1px solid #3C8DBC">
                                
                                    <tr>
                                        <th colspan="2" style="background:#9bd7d5;">
                                            <h3>Tax FY- 2016</h3> 
                                            <h3>Summary Calculation</h3>
                                        </th>
                                    </tr>
                             
                                    <!--tr><th>Taxable salary  </th><td><?php echo $taxcomponents['EmployeeTaxsalsum']['taxable_salary']; ?></td></tr-->
                                    <tr>
                                        <th>Taxable Income from salary </th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['taxable_income']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Taxable Income from Other Sources </th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['other_income']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Investments &amp; Other Deductions</th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['declared_deduction']; ?></td>
                                    </tr>
                             
<!--                                <tbody><tr><th colspan="2">Total Taxable Income   <h5>(Taxable Income from salary + Taxable Income from Other Sources - Deductions)</h5><h2 class="pull-right">360668</h2></th></tr>-->
                             
                                    <tr>
                                        <th>Rs.2,50,000 - Rs.5,00,000 + </th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['first_portion']; ?>
                                        </td></tr>
                                    <tr>
                                        <th>Rs.5,00,000 - Rs.10,00,000 + </th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['second_portion']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Rs.10,00,000 and beyond + </th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['third_portion']; ?></td></tr>
                                    <tr>
                                        <th>Yearly Tax  </th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['tax_yearly']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Current Monthly Tax  </th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['tax_monthly_proj']; ?></td>
                                    </tr>
                             
                            </table>


</td><td class="no-border">
 <table class="table table-bordered" style="border:1px solid #3C8DBC">
                                <tr>
                                    <th colspan="2" style="background:#9bd7d5;">
                                        <h3>House Rent Allowance</h3>
                                    </th></tr>
                               
                                    <tr>
                                        <th>40/50 % of Basic</th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['hra1']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Actual HRA Received</th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['hra2']; ?></td>
                                    </tr>

                                    <tr>
                                        <th>Rent Paid - (10 % of Basic)</th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['hra3']; ?></td>
                                    </tr>
                            
                            
                                    <tr>
                                        <th>HRA Exemption</th>
                                        <th><?php echo $value['EmployeeTaxsalsum']['hra3']; ?></th>
                                    </tr>
                            
                                <tr>
                                    <th colspan="2" style="background:#9bd7d5;">
                                        <h3>Salary Details</h3>
                                    </th>
                                </tr>
                               
<!--                                    <tr><th>Actual salary Received</th><td></td></tr>-->
                                    <tr>
                                        <th> Projected Salary</th>
                                        <td><?php echo $value['EmployeeTaxsalsum']['availed_salary']; ?></td>
                                    </tr>
                               
                            </table>
</td></tr></table>
       
            <?php }
        }
        ?>

 

<?php } ?>