<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
    <legend>Attendance Employees Report</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1; 
                  ?>
                <div class="box-body">
                    <fieldset> 
                        <legend>Attendance Reports of <?php  echo isset($value['summary']['0']['0']['branch_name'])? $value['summary']['0']['0']['branch_name'] : '' ; ; ?>  </legend>
              
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
                                  <th>SL NO</th>
                                  <th>Employee ID</th>
                                   <th>Employee Name</th>
                                      <th>Designation</th>
                                       <th>Date Of Joining</th>
                                  <th>Departments</th>
                               
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                 <?php foreach($arr_dates as $key=> $date)
                                 {
                                 ?>
                                  <th><?php echo $date ; ?></th>
                                <?php
                                }
                                ?>
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; ?>
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $k=1;
                                    foreach($arr_data as $val){ ?>
                                        <tr> 
                                            <td><?php  echo $k; ?></td>
                                             <td><?php echo $val['0']['employee_id']; ?></td>
                                             <td><?php echo $val['0']['EmpName']; ?></td>
                                            <td><?php echo $val['0']['designation']; ?></td>
                                             <td><?php echo $val['0']['joining_date']; ?></td>
                                            <td><?php echo $val['0']['department']; ?></td>
                                          
                                         <?php foreach($arr_dates as $key=> $date)
                                            {
                                            ?>       
                                            <td> 
                                                <?php 
                                                $newIndex='FIELD'.($key+1);
                                                echo $val['0'][$newIndex]; 
                                                ?></td>                                          
                                            <?php } ?>
                                        </tr>
                                    <?php 
                                    $k++;
                                            } ?>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this shift</td>
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
                <a href="#" class="btn btn-default" onclick="downloadReport('AttendanceRep','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('AttendanceRep','excel');"><i class="icon-file"></i>Download As Excel</a>
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
        width: 100%;
        max-width: 100%;
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
'title'=>'Attendance Employees Report'));
?>

                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1; 
                  ?>
                <bookmark class="box-body">
             
                        <h4>Attedance Reports of <?php  echo isset($value['summary']['0']['0']['branch_name'])? $value['summary']['0']['0']['branch_name'] : '' ; ; ?>  
                      For the Month : <?php if(isset($month)){ echo $month;}?> </h4>
                                             
                    <br>
                      <table align='center'>
                            <thead>
                              <tr>
                                    <th>Slno</th>
                                    <th>Employee ID</th>
                                  <th>Employee Name</th>
                                      <th>Designation</th>
                                       <th>Date Of Join</th>
                                  <th>Departments</th>
                               
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                 <?php foreach($arr_dates as $key=> $date)
                                 {
                                 ?>
                                  <th><?php echo $date ; ?></th>
                                <?php
                                }
                                ?>
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; ?>
                                <?php if(count($arr_data)>=0){  $j=0;?>
                                    <?php foreach($arr_data as $val){ $j++; ?>
                                        <tr> 
                                            <td><?php echo $j; ?></td>
                                             <td><?php echo $val['0']['employee_id']; ?></td>
                                            <td><?php echo $val['0']['EmpName']; ?></td>
                                            <td><?php echo $val['0']['designation']; ?></td>
                                             <td><?php echo $val['0']['joining_date']; ?></td>
                                            <td><?php echo $val['0']['department']; ?></td>
                                                     <?php foreach($arr_dates as $key=> $date)
                                            {
                                            ?>       
                                            <td> 
                                                <?php 
                                                $newIndex='FIELD'.($key+1);
                                                echo $val['0'][$newIndex]; 
                                                ?></td>                                          
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this shift</td>
                                        </tr>  
                                <?php } ?>
                            </tbody>
                        </table>
	
                    <br>
                               </bookmark>
                   <?php  } ?> <!-- /.box-body -->
           
<?php } ?>