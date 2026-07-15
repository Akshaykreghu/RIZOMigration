<style>
div.inflow {

}
div.positioner {position: absolute; right: 0;} /*may not be  needed: see below*/
div.fixed {
overflow: auto;

}
/*tr td.fix{
    position:fixed;
}*/
</style>
<?php if ($mode == '') { 
     if($reporttype == 'Attendance'){
        $reporttype = 'Attendance';
    }else{
       $reporttype = 'Attendance Register_New'; 
    }
    ?>
    <div class="modal-body">
        <legend style="text-align: center; "><b><?php echo "Attendance Register_New - " .$date?> </b></legend>
          <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")"?>  </h2>
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    $i = 0;
             //  debug($present_employees);
                    if(count($present_employees) == 0){ 
                   echo '<div style="font-size: 20px;text-align:left; background-color:;">
                   There is no data available under the selected criteria.</div>';
                     // die();
                   }else{
                    foreach ($present_employees as $value) {

                      // debug($value);
                        $i += 1;
                        if(!empty($value['summary']))
                        {
                        ?>
                        
                        <div class="box-body">
                            <fieldset>
                                <?php
                                if($criteria == "Units")
                                {
                                ?>
                                <legend> <?php echo isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : '    (No Datas Found Under This Branch)'; ?>  </legend>
                                <?php
                                }
                                else
                                {
                              $empstatus = (isset($value['summary']['0']['EmployeeDetails']['status'])) && $value['summary']['0']['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';
                                    ?>
                              
                                    <legend>  <?php echo isset($value['summary']['0']['Info']['EmpName']) ? $value['summary']['0']['Info']['EmpName'].' '.$empstatus : ''; ?>  </legend>
                                <?php
                                    }
                                ?>
                                <!--       <div class="row">
                                           <div class="col-md-4">
                                               Present Days : <?php // echo $val['AttendanceRegister']['days_present'];  ?>
                                           </div>
                                           <div class="col-md-4">
                                               Leave days : <?php // echo $val['AttendanceRegister']['days_leave'];  ?>
                                           </div>
                                           <div class="col-md-4">
                                               Holidays : <?php // echo $val['AttendanceRegister']['days_holidays'];  ?>
                                           </div>
                                       </div> -->
                                <div class="row">
                                    <div class="col-md-12">

                                    </div>
                                </div>


                            </fieldset>
                            <br>
                            <fieldset>

                                <div class="inflow">
                                        <div class="fixed">
                                <table class="table table-bordered" id="attendanceTable">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Employee ID</th>
                                            <th>Company ID</th>
                                            <th>Employee Name</th>
                                            <th>Joining Date</th>
                                            <th>Branch</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            
                                            <th>Termination Date</th>
                                            
                                        <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->

                                            <th>Present Days</th>
                                            <th>Leave Days</th>
                                            <th>Week off</th>
                                            <th>Holidays</th>
                                            <th>LOP</th>
                                            <?php
                                            foreach ($arr_dates as $key => $date) {
                                                ?>
                                                <th colspan="2"><?php echo $date; ?></th>
                                                <?php
                                            }
                                            ?>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $arr_data = $value['summary']; ?>
        <?php if (count($arr_data) >= 0) { ?>
            <?php
            $k=1;

            foreach ($arr_data as $val) {

                        // $date1 = (date("Y-m", strtotime($month)));
                        // if($val['Termination']['last_approved_working_date']!= null)
                        // $term_date = (date("Y-m",strtotime($val['Termination']['last_approved_working_date'])));
                    
                
            
            
            if($val['AttendanceRegister']['isdelete']=='N'){
                $verifiedstatus='Verified';
            }
            else if($val['AttendanceRegister']['isdelete']=='Y'){
                $verifiedstatus='Not Verified';
            }

         //   if ($date1 <= $term_date || $term_date == null) {
                                    
            // To find LOP
            $count = 0;
            foreach ($arr_dates as $key => $date) {
                $newIndex = 'FIELD' . ($key + 1);
                $dta = $val['AttendanceRegister'][$newIndex];
                    // debug($dta);
                   
            //         // Edited bu Akshay  
            //     if($dta == null || $dta == 'A/A' || $dta == 'LOP' || $dta == 'LOP/LOP' || $dta == 'A/LOP' || $dta == 'LOP/A'){
            //             $count = $count + 2;
            //             // debug($count);
            //     }
            //     elseif(strpos($dta,'A/') != false|| strpos($dta,'/A') != false || strpos($dta,'LOP/') != false || strpos($dta,'/LOP') != false || $dta == 'A/WO' || $dta == 'WO/A'){
            //                                     $count = $count + 1;
            //                         }
                

            // }

                   $arr = explode("/",$dta);
                                          
                                        // debug($arr);

                                    
                                    foreach($arr as $ar){
                                        if($ar == null || $ar == 'A' || $ar == 'LOP' || $ar == '' ){
                                            if(count($arr) == 2){
                                                $count = $count+1;
                                            }
                                            elseif(count($arr) == 1){
                                                $count = $count+2;
                                            }
                                            

                                        }
                                    }     
            
                                }

            $count = $count / 2;

                                 
            ?>
                                                <tr> 
                                                    <td><?php echo $k;?></td>
                                                    <td><?php echo $val['Info']['employee_id']; ?></td>
                                                    <td><?php echo $val['Info']['emp_id']; ?></td>
                                                    <td><?php echo $val['Info']['EmpName']; ?><?php echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                                                    <?php $join = $val['Info']['joining_date'];
                                                   $join_date = date('d-m-Y', strtotime($join));
                                                  $termination = $val['Termination']['last_approved_working_date'];


$termin_date = !empty($termination) ? date('d-m-Y', strtotime($termination)) : '';


?>
                                                     <td><?php echo $join_date; ?></td>
                                                    <td><?php echo $val['Info']['branch']; ?></td>
                                                    <td><?php echo $val['Info']['department']; ?></td>
                                                    <td><?php echo $val['Info']['designation']; ?></td>
                                                   
                                                    <td><?php echo $termin_date; ?></td>
                                                    <td><?php echo $val['AttendanceRegister']['days_present']; ?></td>
                                                    <td> <?php echo $val['AttendanceRegister']['days_leave']; ?></td>
                                                    <td> <?php echo $val['AttendanceRegister']['days_holidays']; ?></td>
                                                    <td> <?php echo $val['AttendanceRegister']['HO']; ?></td>
                                                    <td> <?php echo $count; ?></td>
                                                    
                                                    <?php
                                                    // debug($arr_dates); 
                                                    foreach ($arr_dates as $key => $date) {
                                                        ?>       
                                                        <!-- <td>  -->
                                                            <?php
                                                            $newIndex = 'FIELD' . ($key + 1);
                                                            $dta = $val['AttendanceRegister'][$newIndex];
                                                            $pieces = array();
                                                            
                                                            // echo $val['AttendanceRegister'][$newIndex];
                                                            if(strpos($dta,'/')!==false){
                                                                $pieces = explode('/',$dta);?>
                                                                <td><?php echo $pieces[0]?></td>
                                                                <td><?php echo $pieces[1]?></td>
                                                            <?php
                                                            }else{?>
                                                                <td><?php echo $dta?></td>
                                                                <td><?php echo $dta?></td>
                                                                
                                                          <?php }
                                                            ?>
                                                            <!-- </td>                                           -->
                                                <?php } ?>
                                                        
                                                    <td> <?php echo $verifiedstatus?></td>
                                                </tr>
            <?php 
            $k++;
                    
                                                    } ?>
        <?php } else { ?>
                                            <tr>
                                                <td colspan="4">No employees found under this shift</td>
                                            </tr>  
        <?php } ?>
                                    </tbody>
                                </table>
                                        </div>
                                </div>
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
                            <?php $arr_data = $value['employees']; ?>
        <?php if (count($arr_data) >= 0) { ?>
            <?php foreach ($arr_data as $val) { ?>
                                                         <tr> 
                                                             <td><?php echo $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name']; ?></td>
                                                             <td><?php echo $val['EmployeeProfessionalDetails']['designation']; ?></td>
                                                             <td><?php echo $val['Units']['branch_name']; ?></td>
                                                         </tr>
            <?php } ?>
        <?php } else { ?>
                                                     <tr>
                                                         <td colspan="4">No employees found under this shift</td>
                                                     </tr>  
        <?php } ?>
                                     </tbody>
                                 </table>
                             </fieldset> -->
                        </div>
    <?php } } }?> <!-- /.box-body -->
                </div>
            </div>
        </div>    
<!--        <div class="row">
            <div class="form-group">
                <div class="col-md-12" align="right">
                    <a href="#" class="btn btn-default" onclick="downloadReport('Attendance', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                    <a href="#" class="btn btn-default" onclick="downloadReport('Attendance', 'excel');"><i class="icon-file"></i>Download As Excel</a>
                </div>
            </div>
        </div>-->
    </div>

<script>


</script>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';  ?>


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
//    echo $this->element('reportadminheader', array(
//        'title' => 'Attendance Register - '.$report_month));
    ?>
    <?php
    $i = 0;
    if(!empty($arr_leavepolicydetails_for_template))
    {
    foreach ($arr_leavepolicydetails_for_template as $value) {
        $i += 1;
        if(!empty($value['summary']))
                        {
        ?>
        <?php
                                if($criteria == "Units")
                                {
                                ?>
                                <h3>Attendance Register of  <?php echo isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : '    (No Datas Found Under This Branch)'; ?>  </h3>
                                <?php
                                }
                                else
                                {
                                   // $empstatus = (isset($value['summary']['0']['EmployeeDetails']['status'])) && $value['summary']['0']['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';
                                    ?>
                                
                                    <h3>Attendance Register of  <?php echo isset($value['summary']['0']['Info']['EmpName']) ? $value['summary']['0']['Info']['EmpName'] : '    (No Datas Found Under This Employees)'; ?>  </h3>
                                <?php
                                    }
                                ?><br>
        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Employee ID</th>
                                            <th>Company ID</th>
                                            <th>Employee Name</th>
                                            <th>Branch</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Joining Date</th>
                                            <th>Termination Date</th>
                                            
                                        <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->

                                            <th>Present Days</th>
                                            <th>Leave Days</th>
                                            <th>Week off</th>
                                            <th>Holidays</th>
                                            <th>LOP</th>
                                            <?php
                                            foreach ($arr_dates as $key => $date) {
                                                ?>
                                                <th><?php echo $date; ?></th>
                                                <?php
                                            }
                                            ?>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $arr_data = $value['summary']; ?>
        <?php if (count($arr_data) >= 0) { ?>
            <?php
            $k=1;
            foreach ($arr_data as $val) { ?>
            <?php
            if($val['AttendanceRegister']['isdelete']=='N'){
                $verifiedstatus1='Verified';
            }
            else{
                $verifiedstatus1='Not Verified';
            }
            ?>
                                                <tr> 
                                                    <td><?php echo $k;?></td>
                                                    <td><?php echo $val['Info']['emp_id']; ?></td>
                                                    <td><?php echo $val['Info']['employee_id']; ?></td>
                                                    <td><?php echo $val['Info']['EmpName']; ?><?php echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?></td>
                                                    <td><?php echo $val['Info']['branch']; ?></td>
                                                    <td><?php echo $val['Info']['department']; ?></td>
                                                    <td><?php echo $val['Info']['designation']; ?></td>
                                                    <td><?php echo $val['Info']['joining_date']; ?></td>
                                                    <td><?php echo $val['Termination']['last_approved_working_date']; ?></td>
                                                    <td><?php echo $val['AttendanceRegister']['days_present']; ?></td>
                                                    <td> <?php echo $val['AttendanceRegister']['days_leave']; ?></td>
                                                    <td> <?php echo $val['AttendanceRegister']['days_holidays']; ?></td>
                                                    <td> <?php echo $val['AttendanceRegister']['HO']; ?></td>
                                                    <td> <?php echo $val['AttendanceRegister']['lops']; ?></td>

                                                    <?php
                                                    foreach ($arr_dates as $key => $date) {
                                                        ?>       
                                                        <td> 
                                                            <?php
                                                            $newIndex = 'FIELD' . ($key + 1);
                                                            echo $val['AttendanceRegister'][$newIndex];
                                                            ?></td>                                          
                                                <?php } ?>
                                                        
                                                    <td> <?php echo $verifiedstatus1?></td>
                                                </tr>
            <?php 
            $k++;
                                                    } ?>
        <?php } else { ?>
                                            <tr>
                                                <td colspan="4">No employees found under this shift</td>
                                            </tr>  
        <?php } ?>
                                    </tbody>
                                </table>
    <?php } ?>        

<?php }
} else{
        echo '<div style="font-size: 25px;margin-top:30px;text-align:center; background-color:#F7D3D2;">
    There is no data available under the selected criteria.</div>';
} }?>
                                
                                <div id="demo_info">
                                    <!--<div>Search event - 1518605452094</div>-->
                                </div>
                                
                                <input type="hidden" value="sanj" id="searchval">
                                <input type="hidden" id="filterval">

    