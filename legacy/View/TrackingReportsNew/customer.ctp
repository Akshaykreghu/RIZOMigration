<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <h2 style="text-align:center;">Customer Visit Detailed Report - <?php echo $report_month; ?></h2>
         <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
       
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                   

                        <?php
                        $i = 0;
                        foreach ($arr_leavepolicydetails_for_template as $value) {

                            if (empty($value['summary']))
                                continue;
                            $arr_daata = $value['summary'];
                            if ($criterias == 'EmployeeDetails') {
                                ?>
                                <div class="box-body">
                                    <fieldset>  
                                        <legend> Customer Visit Details of  
                                             <?php
                                            echo isset($arr_daata[0]['employee_info']['EmpName']) ? $arr_daata[0]['employee_info']['EmpName'] : '';
                                            ?> 
                                        </legend>
                                    </fieldset>
                                    <br>
                                    <fieldset>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Sl.No</th>
                                                     <th>Customer Name</th>
                                                    <th>Employee ID </th>
                                                    <th>Employee Name</th>
                                                    <th>Branch</th>
                                                    <th>Purpose</th>
                                                    <th>Start Time </th>
                                                    <th>Start Location</th>
                                                    <th>Step in Time</th>
                                                     <th>Step-in Location</th>
                                                    <th>Duration1 </th>
                                                    <th>Start to Step-in Distance</th>
                                                    <th>Step-out Time</th>
                                                    <th>Step-out location</th>
                                                    <th>Duration2 </th>
                                                    <th>Step-in to Step-out Distance</th>
                                                     <th>Total Duration </th>
                                                     <th>Total Distance </th>
                                                     <th>Contact Person </th>
                                                     <th>Contact Number </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                            } 
                                                else {
                                ?>
                                <div class="box-body">
                                    <fieldset>  
                                        <legend>Employee Pay Hours Details of  
                                            <?php
                                            echo isset($arr_daata[0]['employee_info']['branch']) ? $arr_daata[0]['employee_info']['branch'] : '';
                                            ?>
                                        </legend>
                                    </fieldset>
                                    <br>
                                    <fieldset>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Sl.No</th>
                                                     <th>Customer Name</th>
                                                    <th>Employee ID </th>
                                                    <th>Employee Name</th>
                                                    <th>Branch</th>
                                                    <th>Purpose</th>
                                                    <th>Start Time </th>
                                                    <th>Start Location</th>
                                                    <th>Step in Time</th>
                                                     <th>Step-in Location</th>
                                                    <th>Duration1 </th>
                                                    <th>Start to Step-in Distance</th>
                                                    <th>Step-out Time</th>
                                                    <th>Step-out location</th>
                                                    <th>Duration2 </th>
                                                    <th>Step-in to Step-out Distance</th>
                                                     <th>Total Duration </th>
                                                     <th>Total Distance </th>
                                                     <th>Contact Person </th>
                                                     <th>Contact Number </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                            } 

                                            ?>
                                    
                                            <?php if (count($arr_daata) > 0) { ?>
                                                <?php
                                                foreach ($arr_daata as $val) {

                                                    //$i += 1;
                                                    $customer = $val['cus_visit_locations']['customer_name1'];
                            $employee_id = $val['employee_info']['employee_id'];
                            $name = $val['employee_info']['EmpName'];
                            $branch = $val['employee_info']['branch'];
                            $purpose = $val['cus_visit_locations']['purpose'];
                            $StartTime  = isset($val['cus_visit_locations']['start_time']) ? $val['cus_visit_locations']['start_time'] : '';
                            $StartLocation  = isset($val['cus_visit_locations']['start_location']) ? $val['cus_visit_locations']['start_location'] : '';
                            $StepinTime  = isset($val['cus_visit_locations']['stepin_time']) ? $val['cus_visit_locations']['stepin_time'] : '';
                            $StepinLocation  = isset($val['cus_visit_locations']['stepin_location']) ? $val['cus_visit_locations']['stepin_location'] :'' ;
                            $StepoutTime  = isset($val['cus_visit_locations']['stepout_time']) ? $val['cus_visit_locations']['stepout_time'] : '';
                            $stepoutlocation  = isset($val['cus_visit_locations']['stepout_location']) ? $val['cus_visit_locations']['stepout_location'] : '';
                            $contactperson=isset($val['cus_visit_locations']['contact_person']) ? $val['cus_visit_locations']['contact_person'] : '';
                            $contactnumber=isset($val['cus_visit_locations']['contact_number']) ? $val['cus_visit_locations']['contact_number'] : '';
                if(isset($StartTime) && !empty($StartTime)) {  
                $t1 = strtotime($StartTime);
                $t2 = strtotime($StepinTime);
                $t3 = strtotime($StepoutTime);
                //duration 1
                $delta_T = ($t2 - $t1);  
                $minutes = floor(((($delta_T % 604800) % 86400) % 3600) / 60); 
                $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));
                $fullDays    = floor($delta_T/(60*60*24));
                $fullHours   = floor(($delta_T-($fullDays*60*60*24))/(60*60)) + ($fullDays * 24);
                $Duration= $fullHours." Hour ".$minutes." Min  " .$sec ." Sec";  
                //duration 2 
                $delta_T1 = ($t3 - $t2);
                $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60); 
                $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                $fullDays1    = floor($delta_T1/(60*60*24));
                $fullHours1   = floor(($delta_T1-($fullDays1*60*60*24))/(60*60)) + ($fullDays1 * 24);
                $Duration1 =  $fullHours1." Hour ".$minutes1." Min  " .$sec1 ." Sec";
                //total duration
                $delta_T2 = ($t3 - $t1);
                $minutes2 = floor(((($delta_T2 % 604800) % 86400) % 3600) / 60); 
                $sec2 = round((((($delta_T2 % 604800) % 86400) % 3600) % 60));
                $fullDays2    = floor($delta_T2/(60*60*24));
                $fullHours2   = floor(($delta_T2-($fullDays2*60*60*24))/(60*60)) + ($fullDays2 * 24);
                $TotalDuration = $fullHours2." Hour ".$minutes2." Min  " .$sec2 ." Sec";
                 }
                else {
                $Duration=0;
                $t2 = strtotime($StepinTime);
                $t3 = strtotime($StepoutTime);
                //duration 2 
                $delta_T1 = ($t3 - $t2);
                $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60); 
                $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                $fullDays1    = floor($delta_T1/(60*60*24));
                $fullHours1   = floor(($delta_T1-($fullDays1*60*60*24))/(60*60)) + ($fullDays1 * 24);
                $TotalDuration = $Duration1 =  $fullHours1." Hour ".$minutes1." Min  " .$sec1 ." Sec";
                }
                 
  ////$StarttoStepinDistance 
    if(isset($StartTime) && !empty($StartTime)) { 
     $lat =  isset($val['cus_visit_locations']['start_latitude']) ? $val['cus_visit_locations']['start_latitude'] : '';
     $lon = isset($val['cus_visit_locations']['start_longitude']) ? $val['cus_visit_locations']['start_longitude'] :'';
     $lat1 = isset($val['cus_visit_locations']['stepin_latitude']) ? $val['cus_visit_locations']['stepin_latitude'] : '';
     $lon1 = isset($val['cus_visit_locations']['stepin_longitude']) ? $val['cus_visit_locations']['stepin_longitude']: '';
     //Calculate distance from latitude and longitude
     $theta = $lon1 - $lon;
     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
     $dist = acos($dist);
     $dist = rad2deg($dist);
     $miles = $dist * 60 * 1.1515;
     $distance = round(($miles * 1.609344),2);
     if($miles == 'NAN'){$distance = 0;}
     $StarttoStepinDistance = isset($distance) ? $distance : ''; 
     }
     else{
         $StarttoStepinDistance=0;
     }

  //StepintoStepoutDistance
 if(isset($StepinTime) && !empty($StepinTime)) {  
        $lat3 = isset($val['cus_visit_locations']['stepin_latitude']) ? $val['cus_visit_locations']['stepin_latitude'] : '' ;
        $lon3 = isset($val['cus_visit_locations']['stepin_longitude']) ? $val ['cus_visit_locations']['stepin_longitude'] : '';
        $lat4 = isset($val['cus_visit_locations']['stepout_latitude']) ? $val['cus_visit_locations']['stepout_latitude'] : '';
        $lon4 = isset($val['cus_visit_locations']['stepout_longitude']) ? $val['cus_visit_locations']['stepout_longitude'] : '';
      //Calculate distance from latitude and longitude
      $theta1 = $lon4 - $lon3;
      $dist1 = sin(deg2rad($lat4)) * sin(deg2rad($lat3)) +  cos(deg2rad($lat4)) * cos(deg2rad($lat3)) * cos(deg2rad($theta1));
      $dist1 = acos($dist1);
      $dist1 = rad2deg($dist1);
      $miles1 = $dist1 * 60 * 1.1515;
      $distance1 = round(($miles1 * 1.609344),2);
      if($miles1 == 'NAN'){$distance1 = 0;}
      $StepintoStepoutDistance = isset($distance1) ? $distance1 : ''; 
      }
      else{
          $StepintoStepoutDistance =0;
      }
  
 $TotalDistance = ($StarttoStepinDistance + $StepintoStepoutDistance) ;
                                                    ?>
                                                    <tr>  
                                                        <td><?php echo $i ?></td>
                                                        <td><?php echo $customer; ?></td>
                                                        <td><?php echo $employee_id; ?></td>
                                                        <td><?php echo $name; ?></td>
                                                        <td><?php echo $branch; ?></td>
                                                        <td><?php echo $purpose; ?></td>
                                                        <td><?php echo $StartTime ?></td>
                                                        <td><?php echo $StartLocation; ?></td>
                                                        <td><?php echo $StepinTime; ?></td>
                                                        <td><?php echo $StepinLocation; ?></td>
                                                        <td><?php echo $Duration; ?></td>
                                                        <td><?php echo $StarttoStepinDistance.' km'; ?></td>
                                                        <td><?php echo $StepoutTime ?></td>
                                                        <td><?php echo $stepoutlocation; ?></td>
                                                        <td><?php echo $Duration1; ?></td>
                                                        <td><?php echo $StepintoStepoutDistance.' km'; ?></td>
                                                        <td><?php echo $TotalDuration; ?></td>
                                                        <td><?php echo $TotalDistance.' km'; ?></td>
                                                      <td><?php echo $contactperson; ?></td>
                                                      <td><?php echo $contactnumber; ?></td>
                                                    </tr>
                                                <?php $i++;} ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="6">No employees found under this Site</td>
                                                </tr>  
                                            <?php } ?>
                                            <?php if ($criterias == 'Units') { ?>
                                            </tbody>
                                        </table>
                                    </fieldset>
                                </div>
                            <?php } ?>

                        <?php } ?> <!-- /.box-body -->
                        <?php if ($criterias == 'EmployeeDetails') {?>
                            </tbody>
                        </table>
                        <?php
                    }
                    if (empty($arr_leavepolicydetails_for_template)) {
                        ?> <!-- /.box-body -->
                        <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
                    <?php } ?> 
                </div>
            </div>
        </div>   
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';     ?>
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
            width: 120%;
            max-width: 120%;
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
       
       th.startlocation, td.startlocation {
        width: 10% !important; /* Increased width */
    }
    th.slno, td.slno {
        width: 3% !important; /* Increased width */
    }
     th.custname, td.custname {
        width: 3% !important; /* Increased width */
    }
    </style>
    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Customer Visit Detailed Reports : ' . $report_month));
    ?>

    <div class="row">
            <div class="col-md-12">
                <div class=" ">
                   

                        <?php
                        $i = 0;
                        foreach ($arr_leavepolicydetails_for_template as $value) {

                            if (empty($value['summary']))
                                continue;
                            $arr_daata = $value['summary'];
                            if ($criterias == 'EmployeeDetails') {
                                ?>
                                <div class="box-body">
                                    <fieldset>  
                                        <legend> Customer Visit Details of  
                                             <?php
                                            echo isset($arr_daata[0]['employee_info']['EmpName']) ? $arr_daata[0]['employee_info']['EmpName'] : '';
                                            ?> 
                                        </legend>
                                    </fieldset>
                                    <br>
                                    <fieldset>
                                        <table class="table table-bordered">
                                            <thead>
                                <tr>
                    <th>Sl.No</th>
                    <th>Customer Name</th>
                    <th>Employee ID</th>
                    <th >Employee Name</th>
                    <th >Branch</th>
                    <th >Purpose</th>
                    <th >Start Time</th>
                    <th  class="startlocation">Start Location</th>
                    <th>Step-in Time</th>
                    <th >Step-in Location</th>
                    <th >Duration1</th>
                    <th >Start to Step-in Distance</th>
                    <th >Step-out Time</th>
                    <th>Step-out Location</th>
                    <th >Duration2</th>
                    <th >Step-in to Step-out Distance</th>
                    <th>Total Duration</th>
                    <th>Total Distance</th>
                    <th >Contact Person</th>
                    <th >Contact Number</th>
                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                            } 
                                                else {
                                ?>
                                <div class="box-body">
                                    <fieldset style="border:none;">  
                                        <legend style="border:none;">Employee Pay Hours Details of  
                                            <?php
                                            echo isset($arr_daata[0]['employee_info']['branch']) ? $arr_daata[0]['employee_info']['branch'] : '';
                                            ?>
                                        </legend>
                                    </fieldset>
                                  
                                    <fieldset>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Sl.No</th>
                                                     <th>Customer Name</th>
                                                    <th>Employee ID </th>
                                                    <th>Employee Name</th>
                                                    <th>Branch</th>
                                                    <th>Purpose</th>
                                                    <th>Start Time </th>
                                                    <th class="startlocation">Start Location</th>
                                                    <th>Step in Time</th>
                                                     <th>Step-in Location</th>
                                                    <th>Duration1 </th>
                                                    <th>Start to Step-in Distance</th>
                                                    <th>Step-out Time</th>
                                                    <th>Step-out location</th>
                                                    <th>Duration2 </th>
                                                    <th>Step-in to Step-out Distance</th>
                                                     <th>Total Duration </th>
                                                     <th>Total Distance </th>
                                                     <th>Contact Person </th>
                                                     <th>Contact Number </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                            } 

                                            ?>
                                    
                                            <?php if (count($arr_daata) > 0) { ?>
                                                <?php
                                                foreach ($arr_daata as $val) {

                                                    //$i += 1;
                                                    $customer = $val['cus_visit_locations']['customer_name1'];
                            $employee_id = $val['employee_info']['employee_id'];
                            $name = $val['employee_info']['EmpName'];
                            $branch = $val['employee_info']['branch'];
                            $purpose = $val['cus_visit_locations']['purpose'];
                            $StartTime  = isset($val['cus_visit_locations']['start_time']) ? $val['cus_visit_locations']['start_time'] : '';
                            $StartLocation  = isset($val['cus_visit_locations']['start_location']) ? $val['cus_visit_locations']['start_location'] : '';
                            $StepinTime  = isset($val['cus_visit_locations']['stepin_time']) ? $val['cus_visit_locations']['stepin_time'] : '';
                            $StepinLocation  = isset($val['cus_visit_locations']['stepin_location']) ? $val['cus_visit_locations']['stepin_location'] :'' ;
                            $StepoutTime  = isset($val['cus_visit_locations']['stepout_time']) ? $val['cus_visit_locations']['stepout_time'] : '';
                            $stepoutlocation  = isset($val['cus_visit_locations']['stepout_location']) ? $val['cus_visit_locations']['stepout_location'] : '';
                            $contactperson=isset($val['cus_visit_locations']['contact_person']) ? $val['cus_visit_locations']['contact_person'] : '';
                            $contactnumber=isset($val['cus_visit_locations']['contact_number']) ? $val['cus_visit_locations']['contact_number'] : '';
                if(isset($StartTime) && !empty($StartTime)) {  
                $t1 = strtotime($StartTime);
                $t2 = strtotime($StepinTime);
                $t3 = strtotime($StepoutTime);
                //duration 1
                $delta_T = ($t2 - $t1);  
                $minutes = floor(((($delta_T % 604800) % 86400) % 3600) / 60); 
                $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));
                $fullDays    = floor($delta_T/(60*60*24));
                $fullHours   = floor(($delta_T-($fullDays*60*60*24))/(60*60)) + ($fullDays * 24);
                $Duration= $fullHours." Hour ".$minutes." Min  " .$sec ." Sec";  
                //duration 2 
                $delta_T1 = ($t3 - $t2);
                $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60); 
                $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                $fullDays1    = floor($delta_T1/(60*60*24));
                $fullHours1   = floor(($delta_T1-($fullDays1*60*60*24))/(60*60)) + ($fullDays1 * 24);
                $Duration1 =  $fullHours1." Hour ".$minutes1." Min  " .$sec1 ." Sec";
                //total duration
                $delta_T2 = ($t3 - $t1);
                $minutes2 = floor(((($delta_T2 % 604800) % 86400) % 3600) / 60); 
                $sec2 = round((((($delta_T2 % 604800) % 86400) % 3600) % 60));
                $fullDays2    = floor($delta_T2/(60*60*24));
                $fullHours2   = floor(($delta_T2-($fullDays2*60*60*24))/(60*60)) + ($fullDays2 * 24);
                $TotalDuration = $fullHours2." Hour ".$minutes2." Min  " .$sec2 ." Sec";
                 }
                else {
                $Duration=0;
                $t2 = strtotime($StepinTime);
                $t3 = strtotime($StepoutTime);
                //duration 2 
                $delta_T1 = ($t3 - $t2);
                $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60); 
                $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                $fullDays1    = floor($delta_T1/(60*60*24));
                $fullHours1   = floor(($delta_T1-($fullDays1*60*60*24))/(60*60)) + ($fullDays1 * 24);
                $TotalDuration = $Duration1 =  $fullHours1." Hour ".$minutes1." Min  " .$sec1 ." Sec";
                }
                 
  ////$StarttoStepinDistance 
    if(isset($StartTime) && !empty($StartTime)) { 
     $lat =  isset($val['cus_visit_locations']['start_latitude']) ? $val['cus_visit_locations']['start_latitude'] : '';
     $lon = isset($val['cus_visit_locations']['start_longitude']) ? $val['cus_visit_locations']['start_longitude'] :'';
     $lat1 = isset($val['cus_visit_locations']['stepin_latitude']) ? $val['cus_visit_locations']['stepin_latitude'] : '';
     $lon1 = isset($val['cus_visit_locations']['stepin_longitude']) ? $val['cus_visit_locations']['stepin_longitude']: '';
     //Calculate distance from latitude and longitude
     $theta = $lon1 - $lon;
     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
     $dist = acos($dist);
     $dist = rad2deg($dist);
     $miles = $dist * 60 * 1.1515;
     $distance = round(($miles * 1.609344),2);
     if($miles == 'NAN'){$distance = 0;}
     $StarttoStepinDistance = isset($distance) ? $distance : ''; 
     }
     else{
         $StarttoStepinDistance=0;
     }

  //StepintoStepoutDistance
 if(isset($StepinTime) && !empty($StepinTime)) {  
        $lat3 = isset($val['cus_visit_locations']['stepin_latitude']) ? $val['cus_visit_locations']['stepin_latitude'] : '' ;
        $lon3 = isset($val['cus_visit_locations']['stepin_longitude']) ? $val ['cus_visit_locations']['stepin_longitude'] : '';
        $lat4 = isset($val['cus_visit_locations']['stepout_latitude']) ? $val['cus_visit_locations']['stepout_latitude'] : '';
        $lon4 = isset($val['cus_visit_locations']['stepout_longitude']) ? $val['cus_visit_locations']['stepout_longitude'] : '';
      //Calculate distance from latitude and longitude
      $theta1 = $lon4 - $lon3;
      $dist1 = sin(deg2rad($lat4)) * sin(deg2rad($lat3)) +  cos(deg2rad($lat4)) * cos(deg2rad($lat3)) * cos(deg2rad($theta1));
      $dist1 = acos($dist1);
      $dist1 = rad2deg($dist1);
      $miles1 = $dist1 * 60 * 1.1515;
      $distance1 = round(($miles1 * 1.609344),2);
      if($miles1 == 'NAN'){$distance1 = 0;}
      $StepintoStepoutDistance = isset($distance1) ? $distance1 : ''; 
      }
      else{
          $StepintoStepoutDistance =0;
      }
  
 $TotalDistance = ($StarttoStepinDistance + $StepintoStepoutDistance) ;
                                                    ?>
                                                    <tr>  
                                                        <td><?php echo $i ?></td>
                                                        <td><?php echo $customer; ?></td>
                                                        <td><?php echo $employee_id; ?></td>
                                                        <td><?php echo $name; ?></td>
                                                        <td><?php echo $branch; ?></td>
                                                        <td><?php echo $purpose; ?></td>
                                                        <td><?php echo $StartTime ?></td>
                                                        <td class="startlocation"><?php echo $StartLocation; ?></td>
                                                        <td><?php echo $StepinTime; ?></td>
                                                        <td><?php echo $StepinLocation; ?></td>
                                                        <td><?php echo $Duration; ?></td>
                                                        <td><?php echo $StarttoStepinDistance.' km'; ?></td>
                                                        <td><?php echo $StepoutTime ?></td>
                                                        <td><?php echo $stepoutlocation; ?></td>
                                                        <td><?php echo $Duration1; ?></td>
                                                        <td><?php echo $StepintoStepoutDistance.' km'; ?></td>
                                                        <td><?php echo $TotalDuration; ?></td>
                                                        <td><?php echo $TotalDistance.' km'; ?></td>
                                                      <td><?php echo $contactperson; ?></td>
                                                      <td><?php echo $contactnumber; ?></td>
                                                    </tr>
                                                <?php $i++;} ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="6">No employees found under this Site</td>
                                                </tr>  
                                            <?php } ?>
                                            <?php if ($criterias == 'Units') { ?>
                                            </tbody>
                                        </table>
                                    </fieldset>
                                </div>
                            <?php } ?>

                        <?php } ?> <!-- /.box-body -->
                        <?php if ($criterias == 'EmployeeDetails') {?>
                            </tbody>
                        </table>
                        <?php
                    }
                    if (empty($arr_leavepolicydetails_for_template)) {
                        ?> <!-- /.box-body -->
                        <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
                    <?php } ?> 
                </div>
            </div>
        </div>   
    


<?php } ?>