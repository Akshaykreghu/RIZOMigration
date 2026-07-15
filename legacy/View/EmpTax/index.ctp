<div class="modal-body" style="overflow-y: auto;">
    <legend>Tax structure</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                   <?php $i=0; foreach (TaxHeads as $value) {
                     
                   $i += 1; 
                  ?>
                <div class="box-body">
                    <fieldset> 
                            <legend> <?php  echo isset($value['summary']['0']['ed']['first_name'])?$value['summary']['0']['ed']['first_name'] : '' ; echo ' ' ; echo  isset($value['summary']['0']['ed']['last_name'])?$value['summary']['0']['ed']['last_name'] : '' ;?>  </legend>
                        <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                                <div class="col-md-4">Branch : <?php echo  isset($value['summary']['0']['br']['branch_name'])?$value['summary']['0']['br']['branch_name'] : '' ; ?>  </div>              
                                <div class="col-md-4">Designation : <?php echo  isset($value['summary']['0']['desg']['desig_name'])?$value['summary']['0']['desg']['desig_name'] : '' ; ?>  </div>               
                                <div class="col-md-4">Department : <?php echo  isset($value['summary']['0']['dpt']['dept_name'])?$value['summary']['0']['dpt']['dept_name'] : '' ; ?>  </div>
                      
                    </fieldset>
                   
                    <br>
                    <fieldset>
		

			    <table class="table table-bordered">
                            <thead>
                              <tr>
                                
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                  <th>Salary</th>
                                  <th>Amount</th>
<!--                                  <th>Head Operator</th>-->
                                  
<!--                                  <th>Item Part</th>-->
<!--                                  <th>Leave days</th>
                                  <th>Holidays</th>-->
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                                 <?php $arr_data  = $value['summary'];  ?>
                                <?php if(count($arr_data)>=0){ $sum = 0; ?>
                                    <?php foreach($arr_data as $val){ 
                                        ?>
                                            <tr> <?php $sum = $sum + $val['ectc']['structure_det_value']; ?>
                                             <td><?php echo $val['ectc']['salary_head_item_desc']; ?></td>
                                            <td><?php echo $val['ectc']['structure_det_value']; ?></td>
<!--                                            <td><?php echo $val['ectc']['head_operator']; ?></td>-->
                                            
<!--                                            <td><?php echo $val['ectc']['item_part']; ?></td>-->
                                            </tr>
                                      
                                    <?php } ?>
                                          <tr>
                                            <th>Grand Total</h><th><?php echo $sum ; ?></th>
                                        </tr>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this data</td>
                                        </tr>  
                                <?php } ?>
                                     
                              
                            </tbody>
                        </table>
		
                    </fieldset>
                    <br>
                               </div>
                
                   <?php  } ?> <!-- /.box-body -->
            </div>
        </div>
    </div>    
  <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>
</div>