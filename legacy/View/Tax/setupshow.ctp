<div class="box box-body">
<!-- Form Name -->
<legend>Details</legend>
<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Employee/Finyear" id="empsetuptaxform">
                    <div class="modal-body">
                        <div class="col-md-12"> 
                       <div class="box-header">
                      
              <h3 class="box-title">Income from salary</h3>

            </div>
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
                        </div>
                        <!--div class="col-md-6">
                            <div class="box-header">
              <h3 class="box-title">Deductions from declaration</h3>

            </div>
                          <table class="table table-bordered">
                            <thead>
                              <tr>
                                
                             <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                  <!--th>Total Deduction</th>
                                  
                               
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                              <td><?php echo $tottax ; ?></td>
                                     
                              
                            </tbody>
                        </table>
                        </div>
                       <!--div class="col-md-6">
                       <table class="table table-bordered">
                            <thead>
                              <tr-->
                                
                             <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                   <!--th>Nature</th>
                                  <th>Total</th>
                                </tr>
                            </thead>
                                    <tbody>
                                    <tr>
                                    <td>Income from Salary</td>
                                    <td><?php echo $Total ; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Income from Other Sources</td><td><?php echo $other_sources ; ?></td>
                                    </tr>
                                    
                                    <tr>
                                    <td>Deductions</td>
                                    <td><?php echo $tottax ; ?></td>
                                    </tr>
                                    <tr><th>Gross Taxable Income</th><th><?php echo $Total + $other_sources + $tottax ; ?></th></tr>
                                    <?php
                                    $ti = $Total + $other_sources + $tottax ;
                                    $taxes1 = 0;
                                    $taxes2 = 0;
                                    $taxes3 = 0;
                                    $taxincome = 0;

                                    if($ti > 250000)    
                                    {   
                                        if($ti > 500000)
                                        {
                                        $taxincome = 250000;
                                        }
                                        else
                                        {
                                        $taxincome = $ti - 250000;
                                        }
                                        $taxes1 = $taxincome * 10 / 100;
                                    }
                                    if($ti > 500000)
                                    {   
                                        if($ti > 1000000)
                                        {
                                        $taxincome = 500000;
                                        }
                                        else
                                        {
                                        $taxincome = $ti - 500000;
                                        }
                                        $taxes2 = $taxincome * 20 / 100;
                                        
                                    }
                                    if($ti > 1000000)
                                    {
                                        $taxincome = $ti - 1000000;
                                        $taxes3 = $taxincome * 30 / 100;
                                    }
                                    ?>
                                    <tr>
                                    <th>
                                    Tax Payable for the year 
                                    </th>
                                    <th>
                                    <?php echo $taxes1 + $taxes2 + $taxes3 ; ?>
                                    </th>
                                    </tr>
                                    <tr>
                                    <th>
                                    Tax Payable for month 
                                    </th>
                                    <th>
                                    <?php echo ($taxyr = $taxes1 + $taxes2) /12 ; ?>
                                    </th>
                                    </tr>
                                    </tbody>
                       </table>
                       </div-->
                    
                    
                    <div class="modal-footer">
                        <div class="col-md-3 pull-right">
                                </div>
                       
                    </div>
            </form>
    </div>
    <script>
     $(document).ready(function(){
        
        
</script>