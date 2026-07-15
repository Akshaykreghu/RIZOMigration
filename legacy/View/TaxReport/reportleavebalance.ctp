<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
    <legend>Employees Leave we</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1; 
                  ?>
                <div class="box-body">
                    <fieldset>
                        <legend> Leave Balance Reports of  
                    <?php  
                    
                
                     if($cr == 'Departments')
                        {
                        echo isset($value['summary']['0']['Departments']['dept_name'])?$value['summary'][0]['Departments']['dept_name']:''; 
                        }
                        else  if($cr == 'Units')
                        {
                        echo isset($value['summary'][0]['Units']['branch_name'])?$value['summary'][0]['Units']['branch_name']:''; 
                        }
                        else  if($cr == 'EmployeeDetails')
                        {
                        echo isset($value['summary'][0]['0']['emp_name'])?$value['summary'][0]['0']['emp_name']:''; 
                        }
                        else
                        {
                        echo isset($value['summary'][0]['sh']['leave_type'])?$value['summary'][0]['sh']['leave_type']:''; 
                        
                        }
                        
                        ?>  </legend>
                        <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                       
                      
                    </fieldset>
                    <br>
                    <fieldset>
			

			    <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Employee Name</th>
                                          <th>Employee ID</th>
                                      <th>Designation</th>
                                       <th>Date Of Join</th>
                                  <th>Departments</th>
                                  <th>Branch</th>
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                   <th>leave Type</th>
                                  <th>Allotted leave For The year</th>
                                  <th>Leave Taken</th>
                                  <th>Leave Balance</th>
                                
                                  
                           
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; ?>
                                <?php if(count($arr_data)>0){ ?>
                                    <?php foreach($arr_data as $val){ ?>
                                        <tr> 
                                            <?php  
                                            
                                            $leavetaken = $val['lp']['alloted_leave_forthe_year'] - $val['0']['leavebalance'];
                                            
                                            
                                            
                                            ?>
                                            <td><?php echo $val['0']['emp_name']; ?></td>
                                            <td><?php echo $val['info']['employee_id']; ?></td>
                                            <td><?php echo $val['info']['designation']; ?></td>
                                             <td><?php echo $val['info']['joining_date']; ?></td>
                                            <td><?php echo $val['info']['department']; ?></td>
                                            <td><?php echo $val['info']['branch']; ?></td>
                                            <td><?php echo $val['sh']['leave_type']; ?></td>
                                            <td> <?php echo $val['lp']['alloted_leave_forthe_year']; ?></td>
                                            <td> <?php echo $leavetaken; ?></td>
                                            <td> <?php echo $val['0']['leavebalance']; ?></td>
                                           
                                        </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this Branches</td>
                                        </tr>  
                                <?php } ?>
                            </tbody>
                        </table>
		
                    </fieldset>
                    <br>
                   <!-- <fieldset>
                        <legend>Employee List</legend>
                        <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Name</th>
                                  <th>Designation</th>
                                  <th>Branch</th>
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['employees']; ?>
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php foreach($arr_data as $val){ ?>
                                        <tr> 
                                            <td><?php echo $val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name']; ?></td>
                                            <td><?php echo $val['EmployeeProfessionalDetails']['designation']; ?></td>
                                            <td><?php echo $val['Units']['branch_name']; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this shift</td>
                                        </tr>  
                                <?php } ?>
                            </tbody>
                        </table>
                    </fieldset> -->
                </div>
                   <?php  } ?> <!-- /.box-body -->
            </div>
        </div>
    </div>    
    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('LeaveBalance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('LeaveBalance','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>
</div>
<?php }else{ ?>
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
echo $this->element('reportadminheader',array(
'title'=>'Employees Leave Report'));
?>
       
                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1; 
                  ?>
                <bookmark>
                  
                        <h4> Leave Balance Reports of  
                    <?php  
                    
                
                     if($cr == 'Departments')
                        {
                        echo isset($value['summary']['0']['Departments']['dept_name'])?$value['summary'][0]['Departments']['dept_name']:''; 
                        }
                        else  if($cr == 'Units')
                        {
                        echo isset($value['summary'][0]['Units']['branch_name'])?$value['summary'][0]['Units']['branch_name']:''; 
                        }
                        else  if($cr == 'EmployeeDetails')
                        {
                        echo isset($value['summary'][0]['0']['emp_name'])?$value['summary'][0]['0']['emp_name']:''; 
                        }
                        else
                        {
                        echo isset($value['summary'][0]['sh']['leave_type'])?$value['summary'][0]['sh']['leave_type']:''; 
                        
                        }
                        
                        ?>  </h4>
                                         
			    <table>
                            <thead>
                              <tr>
                                  <th>Employee Name</th>
                                   <th>Employee ID</th>
                                      <th>Designation</th>
                                       <th>Date Of Join</th>
                                  <th>Departments</th>
                                   <th>Branch</th>
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                   <th>leave Type</th>
                                  <th>Allotted leave For The year</th>
                                  <th>Leave Taken</th>
                                  <th>Leave Balance</th>
                                
                                  
                           
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; ?>
                                <?php if(count($arr_data)>0){ ?>
                                    <?php foreach($arr_data as $val){ ?>
                                        <tr> 
                                            <?php  
                                            
                                            $leavetaken = $val['lp']['alloted_leave_forthe_year'] - $val['0']['leavebalance'];
                                            
                                            
                                            
                                            ?>
                                            <td><?php echo $val['0']['emp_name']; ?></td>
                                              <td><?php echo $val['info']['employee_id']; ?></td>
                                            <td><?php echo $val['info']['designation']; ?></td>
                                             <td><?php echo $val['info']['joining_date']; ?></td>
                                            <td><?php echo $val['info']['department']; ?></td>
                                            <td><?php echo $val['info']['branch']; ?></td>
                                            <td><?php echo $val['sh']['leave_type']; ?></td>
                                            <td> <?php echo $val['lp']['alloted_leave_forthe_year']; ?></td>
                                            <td> <?php echo $leavetaken; ?></td>
                                            <td> <?php echo $val['0']['leavebalance']; ?></td>
                                           
                                        </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this Branches</td>
                                        </tr>  
                                <?php } ?>
                            </tbody>
                        </table>
		    <br>
                   </bookmark>
                   <?php  } ?> <!-- /.box-body -->
            
<?php } ?>