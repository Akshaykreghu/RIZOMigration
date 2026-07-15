<?php if( $mode== '' ){$reporttype = 'Edited Attendance'; ?>

<div class="modal-body" style="overflow-y: auto;">
     <h2 style="font-weight: bold; text-align: center; font-size: 30px;"><?php echo "Edited Attendance - " . $mname . "  " . $year; ?></h2>
       <h2 style="font-weight: bold; text-align: center; font-size: 23px;"> <?php echo "(Report Run by " . $user_id . " at " . $date_time . ")"?></h2>
    <div class="row">
        <div class="col-md-12">
            <div class="">
            <?php $count = count($arr_leavepolicydetails_for_template); 
                          ?>
                          <?php if($count == 0){?>
                            <h4 style="text-align:left;color: black;font-size: 20px;">No data available under the selected criteria</h4>
                          <?php } ?>
                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value){
                   
                   $i += 1; 
                    
                  ?>
                <?php 
                if(!empty($value['summary']))
                    // debug($value);
                {?>
                    
                <div class="">
                    <fieldset>
                         <?php
                                if($criteria == "Units")
                                {
                                ?>
                                <legend><?php echo isset($value['summary']['0']['employee_info']['branch']) ? $value['summary']['0']['employee_info']['branch'] : '    (No Datas Found Under This Branch)'; ?> </legend>
                                <?php
                                }
                                else
                                {
                              
                                    ?>
                              
                                    <legend> <?php $status = isset($value['summary']['0']['EmployeeDetails']['status']) && $value['summary']['0']['EmployeeDetails']['status']=="2" ? '(Resigned)':'' ;
                                    echo $value['summary']['0']['employee_info']['EmpName'] .$status;?>  </legend>
                                <?php
                                    }
                                ?>
                 <!--       <div class="row">
                            <div class="col-md-4">
                                Present Days : <?php // echo $val['AttendanceRegister']['days_present']; ?>
                            </div>
                            <div class="col-md-4">
                                Leave days : <?php // echo $val['AttendanceRegister']['days_leave']; ?>
                            </div>
                            <div class="col-md-4">
                                Holidays : <?php // echo $val['AttendanceRegister']['days_holidays']; ?>
                            </div>
                        </div> -->
                       <!--  <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                        -->
                      
                    </fieldset>
                    <br>
                    
            

                <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Sl No </th>
                                  <th>Employee ID</th>
                                  <th>Company ID</th>
                                  <th>Employee Name</th>
                                  <th>Joining Date</th>
                                  <th>Branch</th>
                                  <th>Department</th>
                                  <th>Designation</th>
                                  <th>Termination</th>
                                  <th>Remarks</th>
                                  <th>Attendance Time</th>
                                  <th>Direction</th>
                                  <th>Created Date</th>
                                  <th>Attendance Edited By</th>
                                  <th>Attendance Edited Date and Time</th>
                                  <th>Status</th>
                          
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; 
                               // debug($arr_data);
                                ?> 

                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach($arr_data as $val){ ?>
                                   <?php   $c_date = $val['EditPunchesHist']['creation_date'];
                                    $l_date = $val['EditPunchesHist']['LOGDATE'];
                                    $datetime = new DateTime($l_date);
                                    $formatted_date = $datetime->format('d-m-Y H:i:s');
                                    $datetime = new DateTime($c_date);
                                    $edited_date = $datetime->format('d-m-Y H:i:s');
                                    $join = $val['employee_info']['joining_date'];
                                    $join_date = date('d-m-Y', strtotime($join));
                                    $date = date('d-m-Y', strtotime($l_date)); 
                                    $tdate=$val['termination']['last_approved_working_date'];
                                    $termin_date = date('d-m-Y', strtotime($tdate)); ?> 
                                 
                                        <tr> 
                                            <?php $i += 1; ?>
                                              <td><?php echo $i;?></td>
                                             
                                              <td><?php echo isset($val['user_credentials']['user_id']) ? $val['user_credentials']['user_id'] : ''; ?></td>
                                               <td><?php echo $val['EditPunchesHist']['emp_id']; ?></td>
                                              <td><?php $S= isset($val['employee_info']['emp_status']) && $val['employee_info']['emp_status']=="2" ? '(Resigned)':'' ;echo $val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name'] .$S ;?></td>

                                            <td><?php echo $join_date; ?></td>
                                            <td><?php echo $val['employee_info']['branch']; ?></td>
                                            <td><?php echo $val['employee_info']['department']; ?></td>
                                            <td><?php echo $val['employee_info']['designation'];; ?></td>
                                            <td><?php echo $termin_date; ?></td>
                                            <td><?php echo  isset($val['EditPunchesHist']['C3']) ? $val['EditPunchesHist']['C3'] : ''; ?></td>
                                            <td><?php echo $formatted_date; ?></td>
                                             <td><?php echo $val['EditPunchesHist']['C1']; ?></td>
                                            <td><?php echo $date ?></td>
                                            <td><?php echo $val['EditPunchesHist']['created_by']; ?></td>
                                            <td><?php echo $edited_date; ?></td>
<?php  
 $creationDate = $val['EditPunchesHist']['creation_date'];
$updatedTime = $val['employee_regularaization']['updated_date'];
        
if ($creationDate === $updatedTime) {
    $status = 'Regularized';
} else {
    $status = $val['EditPunchesHist']['status'];
}

switch ($status) {
    case "N":
        $status = 'Inactive';
        break;
    case "Y":
        $status = 'Active';
        break;
   
}
?>


                                   <td><?php echo $status; ?></td> 
                                             

                                            
                                            
                  
                                        </tr>


                                    <?php } ?>
                                
                            </tbody>
                        </table>

                <?php }else{
                                          echo "No data available under the selected criteria"; 
                                          
                              
                    
                 
                    } ?> <!-- /.box-body -->
            </div>
        </div>
    </div>    
<!--    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>-->
</div>
                <?php  }} } else{?>
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
    echo $this->element('reportadminheader',array('title'=>'Edited Attendance'));
    ?>
<!--    <h1 style="font-weight: bold">Edited Attendance Report </h1>-->
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value){
                   
                   $i += 1; 
                   // debug($value);
                  ?>
                <?php 
                if(isset($value['summary']['0']['EmployeeDetails']['first_name']))
                {?>
                    
                <div class="box-body">
                   
                        <h2 style="font-weight: bold">Edited Attendance of  <?php  echo isset($value['summary']['0']['EmployeeDetails']['first_name']) ? $value['summary']['0']['EmployeeDetails']['first_name'].' ' .$value['summary']['0']['EmployeeDetails']['last_name'] : '' ; ?> <?php  echo isset($value['summary']['0']['EmployeeDetails']['status']) && $value['summary']['0']['EmployeeDetails']['status']=="2" ? '(Resigned)':'' ;?></h2>

                 <!--       <div class="row">
                            <div class="col-md-4">
                                Present Days : <?php // echo $val['AttendanceRegister']['days_present']; ?>
                            </div>
                            <div class="col-md-4">
                                Leave days : <?php // echo $val['AttendanceRegister']['days_leave']; ?>
                            </div>
                            <div class="col-md-4">
                                Holidays : <?php // echo $val['AttendanceRegister']['days_holidays']; ?>
                            </div>
                        </div> -->
                        <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                      <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Sl No .</th>
                                  <th>Employee name</th>
                                  <th>Employee ID</th>
                                  <th>Branch</th>
                                  <th>Direction</th>
                                  <th>Remarks</th>
                                  <th>Creation Date</th>
                                  
                                  <th>Attendance Edited By</th>
                                  <th>Attendance Edited Date</th>
                                  
                                  
                                  
                               
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; ?> 

                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach($arr_data as $val){ ?>

                                        <tr> 
                                            <?php $i += 1; ?>
                                             <td><?php echo $i;?></td>
                                             <td><?php echo $val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name'] ;?><?php  echo isset($value['summary']['0']['EmployeeDetails']['status']) && $value['summary']['0']['EmployeeDetails']['status']=="2" ? '(Resigned)':'' ;?></td>
                                            <td><?php echo $val['EditPunchesHist']['emp_id']; ?></td>
                                            <td><?php echo $val['Branch']['branch_name']; ?></td>
                                            <td><?php echo $val['EditPunchesHist']['C1']; ?></td>
                                            <td><?php echo $val['EditPunchesHist']['C3']; ?></td>
                                            <td><?php echo $val['EditPunchesHist']['LOGDATE']; ?></td>
                                            <td><?php echo $val['EditPunchesHist']['created_by']; ?></td>
                                            <td><?php echo $val['EditPunchesHist']['creation_date']; ?></td> 
                                             

                                            
                                            
                  
                                        </tr>


                                    <?php } }else{?>
                                        <tr>
                                     <td colspan="6">No employee found under this criteria.</td>
                                        </tr>
                                    <?php } ?>
                         
                            </tbody>
                        </table>
              <!-- /.box-body -->
            </div>
        </div>
    </div>    
<!--    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>-->
</div>
    
                <?php  }} }?>
         