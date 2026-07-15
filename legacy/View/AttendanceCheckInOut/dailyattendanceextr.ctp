

<?php if( $mode== '' ){?>
<div class="modal-body" style="overflow-y: auto;">
    <h2 style="font-weight: bold;text-align: center;"><?php echo " Attendance Report for - " . $f . ' - ' . $t?>  </h2>
     <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")"?>  </h2>
    <div class="row">
        <div class="col-md-12">
             <div class="">
<?php
// Assuming your grouping logic is already done
// and you have $groupedData ready

if (!empty($arr_breakdata_for_template)) {
   
    foreach ($arr_breakdata_for_template as $date => $employees) {
        $formattedDateHeader = date('d/m/Y', strtotime($date));
        // echo "<h3 style='margin-top:30px;text-align: center;'>Date: " . htmlspecialchars($formattedDateHeader) . "</h3>";
?>
        <table class="table table-bordered" border='1' cellspacing='0' cellpadding='8' style='border-collapse: collapse; width:100%;'>
        <thead style='background-color:#f2f2f2; text-align:center;'>
        <tr>
                <th>Sl. No</th>
                <th>Employee Name</th>
                <th>Date</th>
                <th>Day Type</th>
                <th>Shift Time</th>
                <th>Punch In Time</th>
                <th>In Location</th>
                <th>Punch Out Time</th>
                <th>Out Location</th>
                <th>Status</th>
                <th>Late Punch-in (minutes)</th>
                <th>Late Punch-out (minutes)</th>
                <th>Hours Worked (HH:MM:SS)</th>
              </tr>
        </thead>
        <tbody>
<?php
        if (!empty($employees)) {
            $slno = 1;
            foreach ($employees as $emp) {

                // Format times safely
                $punchIn  = !empty($emp['att_in_time']) ? date("H:i:s", strtotime($emp['att_in_time'])) : 'No Punch In';
                $punchOut = !empty($emp['att_out_time']) ? date("H:i:s", strtotime($emp['att_out_time'])) : 'No Punch Out';
                $shiftTime = !empty($emp['day_time_desc']) ? htmlspecialchars($emp['day_time_desc']) : '-';
                $workedHours = !empty($emp['duration']) ? htmlspecialchars($emp['duration']) : '-';
                $in_location = !empty($emp['in_location']) ? $emp['in_location'] : '-';
                $out_location = !empty($emp['out_location']) ? $emp['out_location'] : '-';
                // Example: You can calculate lateness if you have shift start/end time logic
                $lateIn  = !empty($emp['late_in_minutes']) ? htmlspecialchars($emp['late_in_minutes']) : '0';   // Replace with logic if available 
                $lateOut = !empty($emp['late_out_minutes']) ? htmlspecialchars($emp['late_out_minutes']) : '0';    // Replace with logic if available

                $pairs = [];
$lastIn = null;

if (!empty($emp['punches'])) {
    foreach ($emp['punches'] as $p) {

        $type = strtolower($p[0]['punch_type']) ;
        $time = $p['device_attandance']['LOGDATE'] ;
        $location = $p['device_attandance']['location'] ;

        if ($type === 'in') {
            if ($lastIn) {
                // Output previous IN as unpaired
                $pairs[] = [
                    'in_time' => $lastIn['in_time'],
                    'out_time' => '',
                    'in_location' => $lastIn['in_location'],
                    'out_location' => ''
                ];
            }
            $lastIn = [
                'in_time' => $time,
                'in_location' => $location
            ];
        } elseif ($type === 'out') {
            if ($lastIn) {
                $pairs[] = [
                    'in_time' => $lastIn['in_time'],
                    'out_time' => $time,
                    'in_location' => $lastIn['in_location'],
                    'out_location' => $location
                ];
                $lastIn = null;
            } else {
                // OUT without a preceding IN
                $pairs[] = [
                    'in_time' => '',
                    'out_time' => $time,
                    'in_location' => '',
                    'out_location' => $location
                ];
            }
        }
    }

    // unmatched IN
    if ($lastIn !== null) {
        $pairs[] = [
            'in_time' => $lastIn['in_time'],
            'out_time' => '',
            'in_location' => $lastIn['in_location'],
            'out_location' => ''
        ];
    }
}

?>

               <?php 
               // Fallback: ensure employee is shown even without punches
if (empty($pairs)) {
    $pairs[] = [
        'in_time' => !empty($emp['att_in_time']) ? $emp['att_in_time'] : '',
        'out_time' => !empty($emp['att_out_time']) ? $emp['att_out_time'] : '',
        'in_location' => !empty($emp['in_location']) ? $emp['in_location'] : '',
        'out_location' => !empty($emp['out_location']) ? $emp['out_location'] : ''
    ];
}
            
$rowspan = count($pairs);
               
$firstRow = true;
$noPunchStyle = "background-color: #FFC7CE; color: #9C0006; font-weight: bold;";
$leaveStyle = "background-color: #CCFFCC; color: #006100; font-weight: bold;";

foreach ($pairs as $pair) {
?>
<tr>
    <?php if ($firstRow) { ?>
        <td rowspan="<?php echo $rowspan; ?>" style="text-align:center; vertical-align: middle;"><?php echo $slno++; ?></td>
        <td rowspan="<?php echo $rowspan; ?>" style="vertical-align: middle;"><?php echo htmlspecialchars($emp['EmpName']); ?></td>
        <td rowspan="<?php echo $rowspan; ?>" style="vertical-align: middle;"><?php echo !empty($emp['att_date']) ? date('d/m/Y', strtotime($emp['att_date'])) : $formattedDateHeader; ?></td>
        <td rowspan="<?php echo $rowspan; ?>" style="vertical-align: middle;"><?php echo htmlspecialchars($emp['day_type']); ?></td>
        <td rowspan="<?php echo $rowspan; ?>" style="vertical-align: middle;"><?php echo $shiftTime; ?></td>
    <?php } ?>

    <?php 
        $inStatus = !empty($pair['in_time']) ? date('H:i:s', strtotime($pair['in_time'])) : (!empty($emp['detailed_leave_status']) ? $emp['detailed_leave_status'] : 'No Punch In Time');
        $inStyle = empty($pair['in_time']) ? (!empty($emp['detailed_leave_status']) ? $leaveStyle : $noPunchStyle) : '';
    ?>
    <td style="text-align:center; <?php echo $inStyle; ?>">
        <?php echo $inStatus; ?>
    </td>
    <td><?php echo !empty($pair['in_location']) ? htmlspecialchars($pair['in_location']) : ''; ?></td>

    <?php 
        $outStatus = !empty($pair['out_time']) ? date('H:i:s', strtotime($pair['out_time'])) : (!empty($emp['detailed_leave_status']) ? $emp['detailed_leave_status'] : 'No Punch Out Time');
        $outStyle = empty($pair['out_time']) ? (!empty($emp['detailed_leave_status']) ? $leaveStyle : $noPunchStyle) : '';
    ?>
    <td style="text-align:center; <?php echo $outStyle; ?>">
        <?php echo $outStatus; ?>
    </td>
    <td style="border: 1px solid #b8b8b8;"><?php echo !empty($pair['out_location']) ? htmlspecialchars($pair['out_location']) : ''; ?></td>

    <?php if ($firstRow) { ?>
        <td rowspan="<?php echo $rowspan; ?>" style="text-align:center; vertical-align: middle;"><?php echo htmlspecialchars($emp['status']); ?></td>
        <td rowspan="<?php echo $rowspan; ?>" style="text-align:center; vertical-align: middle;"><?php echo $lateIn; ?></td>
        <td rowspan="<?php echo $rowspan; ?>" style="text-align:center; vertical-align: middle;"><?php echo $lateOut; ?></td>
        <td rowspan="<?php echo $rowspan; ?>" style="text-align:center; vertical-align: middle;"><?php echo $workedHours; ?></td>
    <?php } ?>
</tr>
<?php
    $firstRow = false;
}
?>
         <?php   }
        } else {
            echo "<tr><td colspan='8' style='text-align:center;'>No employee data found for this date.</td></tr>";
        }

        echo "</tbody>";
        echo "</table>";
    }
} else {
    echo "<p>No attendance data found.</p>";
}
?>


             <?php
            if (empty($arr_breakdata_for_template[0]['summary'])){
                        //echo "<h3>No data available under the selected criteria</h3>";
                    } else{?>

               
        
                   <?php $i=0; foreach ($arr_breakdata_for_template as $value){
                   
                   $i += 1; 
//                 debug($value);
                  ?>
                <?php 
              if (isset($value['summary']['0']['EmpName'])) 
                {?>
                
                    
                         <fieldset> 

                             <?php
                                if($cr== 'Units')
                                {
                                ?><legend><?php  echo isset($value['summary']['0']['branch']) ? $value['summary']['0']['branch'] : ''; ?> -  <?php 
                    $empstatus = isset($value['summary']['0']['status']) && $value['summary']['0']['status'] == "2" ? '(Resigned)' : '';
                 echo $value['summary']['0']['EmpName'].$empstatus; ?>  </legend>
                                <?php
                                }
                                else
                                {
                              // $empstatus = (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] =="2" ? '  (Resigned)':'';
                              //       ?>
                              
                                    <legend><?php 
                    $empstatus = isset($value['summary']['0']['status']) && $value['summary']['0']['status'] == "2" ? '(Resigned)' : '';
                    echo $value['summary']['0']['EmpName'].$empstatus;?>  </legend>
                                <?php
                                    }
                                ?>

                        </fieldset>


                <table class="table table-bordered">
                            <thead>
                              <tr>
                                 <th>Sl No</th>
                                 <th>Employee ID</th>
                                 <th>Employee Name</th>
                                 <th>Date</th>
                                 <th>Duration of Break (Minutes)</th>
                              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary'];?> 
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach ($arr_data as $key => $val) {
                                     $name = $val['EmpName'];
                                      $id = $val['eid'];
                                      $date = $val['date'];
                                      $dateatt = date("d-m-Y", strtotime($date));
                                      $duraion = $val['duration'];
                                    ?>

                                        <tr> 
                                            <?php $i += 1; ?>
                                             <td><?php echo $i;?></td>
                                              <td><?php echo $id;?></td>
                                              <td><?php echo $name;?></td>
                                             
                                              <td><?php echo $dateatt;?></td>
                                             
                                             <td><?php echo $duraion;?></td>
                                             
                                            
                                             

                                            
                                            
                  
                                        </tr>


                                    <?php } ?>
                                
                                   
                            </tbody>
                        </table>
                    
                <?php }else{
                                          echo "No employees found under this Criteria"; 
                                          
                              
                    
                 
                } }} } ?> <!-- /.box-body -->
           
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
                <?php  } else{?>
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
<!--   <legend style="font-weight: bold">Attendance Check In Out Report </legend>-->
<?php
echo $this->element('reportadminheader',array(
'title'=>'Employee Late In Report '));
?>
                   <?php $i=0; foreach ($arr_lateindata_for_template as $value){
                   
                   $i += 1; 
//                 debug($value);
                  ?>
            <div> 
                <?php 
                if(isset($value['summary']['0']['latein']['EmpName']))
                {?>
                   <h4 style="font-weight: bold">Late In of <?php  echo isset($value['summary']['0']['latein']['EmpName']) ? $value['summary']['0']['latein']['EmpName'] : '' ; ?> <?php  echo isset($value['summary']['0']['EmployeeDetails']['status']) && $value['summary']['0']['EmployeeDetails']['status']=="2" ? '(Resigned)':'' ;?></h4>

                    <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Sl No.</th>
                                  <th style="width:100px;">Employee Name</th>
                                   <th style="width:80px;">User ID</th>
                                  <th style="width:100px;">Branch</th>
                                  <th style="width:50px;">Date</th>
                                  <th style="width:650px;">Location</th>
                                  <th style="width:50px;">Shift Start Time</th>
                                  <th>Employee In Time</th>
                                  <th style="width:50px;">Late Time</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary'];?> 
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach($arr_data as $val){
                                    $date=$val['latein']['LogDate'];
                                    $dateatt = date("d-m-Y", strtotime($date));
                                    $latetime=$val['latein']['LateTime']; 
                                    $latetimeatt = date('H:i:s', strtotime($latetime));
                                    ?>

                                        <tr> 
                                            <?php $i += 1; ?>
                                            <td><?php echo $i;?></td>
                                            <td style="width:100px;"><?php echo $val['latein']['EmpName'];?></td>
                                            <td style="width:80px;"><?php echo $val['uc']['user_id'];?></td>
                                            <td style="width:100px;"><?php echo $val['br']['branch_name'];?></td>
                                            <td style="width:50px;"><?php echo $dateatt; ?></td>
                                            <td style="width:650px;"><?php echo $val['0']['Location']; ?></td>
                                            <td style="width:50px;"><?php echo $val['latein']['LateInLimit']; ?></td>
                                            <td><?php echo $val['latein']['InTime']; ?></td>
                                            <td style="width:50px;"><?php echo $latetimeatt; ?></td>
                                            
                                        </tr>


                                    <?php } ?>
                                
                                   
                            </tbody>
                        </table>
                    
                <?php }else{
                                          echo "No employees found under this Criteria"; 
                } } ?> <!-- /.box-body -->
                </div> 

                <?php  } } ?>
         