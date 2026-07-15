
<section class="content-header">
    <h1> Cost To Company(CTC) Detail Report </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row" >
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <div class="row" id="DivIdToPrint">
                        <?php 
            
            foreach($salary_detailss as $value)
            {
            
            ?>
                        
            <div class="box-body">
                     
                       <fieldset> 
                            <legend> <?php  echo isset($value['0']['EmployeeDetails']['first_name'])?$value['0']['EmployeeDetails']['first_name'].' '.$value['0']['EmployeeDetails']['last_name']  : '' ; echo ' ' ; ?>  </legend>
                       
                        <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                                <div class="col-md-4">Branch : <?php echo  isset($value['0']['Branch']['branch_name'])?$value['0']['Branch']['branch_name'] : '' ; ?>  </div>              
                                <div class="col-md-4">Designation : <?php echo  isset($value['0']['Designation']['desig_name'])?$value['0']['Designation']['desig_name'] : '' ; ?>  </div>               
                                <div class="col-md-4">Employee ID : <?php echo  isset($value['0']['EmployeeDetails']['emp_id'])?$value['0']['EmployeeDetails']['emp_id'] : '' ; ?>  </div>
                      
                    
                   
                    <hr>
                    
		

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
                                 <?php $arr_data  = $value;  ?>
                                <?php if(count($arr_data)>=0){ $sum = 0; ?>
                                    <?php foreach($arr_data as $val){ 
                                    if($val['EmployeeSalaryStructure']['structure_det_value'] != '0')
                                    {
                                        ?>
                                            <tr> <?php $sum = $sum + $val['EmployeeSalaryStructure']['structure_det_value']; ?>
                                             <td><?php echo $val['EmployeeSalaryStructure']['salary_head_item_desc']; ?></td>
                                            <td><?php echo $val['EmployeeSalaryStructure']['structure_det_value']; ?></td>
<!--                                            <td><?php echo $val['ectc']['head_operator']; ?></td>-->
                                            
<!--                                            <td><?php echo $val['ectc']['item_part']; ?></td>-->
                                            </tr>
                                      
                                    <?php
                                    }} ?>
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
            <?php
            }
            ?>
                        <input type="button" id="btn" value="print" class="btn btn-default">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $("#btn").click(function () {
    var divToPrint=document.getElementById('LeaveDetailsReports');

  var newWin=window.open('','Print-Window');

  newWin.document.open();
  newWin.document.write('<html>');
  newWin.document.write('<head>');
  newWin.document.write('<style>');
  newWin.document.write('table,td,th{border: 1px solid #ddd;text-align:left;}table{border-collapse:collapse;width:100%;}th,td{padding:15px;}');
  newWin.document.write('</style>');
  newWin.document.write('</head>');
  newWin.document.write('<body onload="window.print()"><table>'+divToPrint.innerHTML+'</table></body>');
  newWin.document.write('</html>');
  newWin.document.close();

  setTimeout(function(){newWin.close();},10);
});

  $(document).ready(function () {
      
      
        $('#LeaveDetailsReports').DataTable({
            "paging": true,
            "pageNumber":true,
            "lengthChange": false,
            "searching": true,
            responsive: true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            buttons: [
                 'print'
            ],
            "lengthMenu": [[12, 24, 50, -1], [12, 24, 50, "All"]]
        });
 
    });
</script>