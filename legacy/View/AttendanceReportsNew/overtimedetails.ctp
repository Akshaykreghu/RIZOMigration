<?php if ($mode == '') { ?>
    <div class="modal-body" >
        <div>Note : If the shift policy is Flexible, will not be shown before and after OT minutes.</div>
        <!--edited by sinsiya 18-03-2024-->
       <!-- <h2 style="text-align: left;font-size: 15px;"><?php //echo  "(Report Run by " . $user_id . " at " . $date_time . ")"?>  </h2>-->
        <h2 align="center"><b><?php echo "Overtime - " . $date ?></b> </h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>
<!--        <legend style="text-align:center; font-weight: bold;">Employee Over Time Attendance Report - <?php //echo $date; ?></legend>-->
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php if (count($arr_leavepolicydetails_for_template)> 0) { ?>
                    <?php
                   
                     if($criteria == 'EmployeeDetails'){   
                         foreach($arr_leavepolicydetails_for_template as $val)
                    { 
                             //debug($val);
                        if(count($val['summary'])>0)
                        {
                          //edited by sinsya 19-03-2024
                      //  if($val['summary']['0']['emp_ot_timeattandance']['ot_duration'] >0){     
                            
                    $i = 0;
                  
                        $i += 1;  ?>
                   
                        <div class="box-body" style="overflow-y: auto;">
                            
                            <br>
                            <fieldset>
<div class="row">
                                    <div class="col-md-6">
                                          <legend> <?php echo $val['summary']['0']['Info']['EmpName'];?><?php echo '('.$val['summary']['0']['Info']['employee_id'].')'; ?><?php echo (isset($val['summary']['0']['empdetails']['status'])) && $val['summary']['0']['empdetails']['status'] == "2" ? '-(Resigned)' : ''; ?> </legend>
                                    </div>
<!--    edited by siniya 18-03-2024-->
<div class="col-md-6">
                                          <legend><?php echo $val['summary']['0']['Info']['branch']; ?> </legend>
                                    </div>
                                </div>
<div class="modal-body">
  
     
<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$total = 0; ?>
    <table class="table table-bordered table-responsive" cellspacing="0" border="0">
     <tr><th>Date</th>
     <?php 
       // foreach($val['summary'] as $value)
          // {  
             ?>
          <!-- <td <?php //if ($value['emp_ot_timeattandance']['weekoff']) { ?> style="color:black ;font-weight:bold ;  "<?php //}; ?> 
               <?php //if ($value['emp_ot_timeattandance']['holiday']) { ?> style="color:black ;font-weight:bold ;  " <?php //}; ?> 
                   <?php //if ($value['emp_ot_timeattandance']['present'] == 'A/A') { ?> style="font-weight:bold ;  " <?php //}; ?>
                       <?php //if ($value['emp_ot_timeattandance']['present'] == '') { ?> style="font-weight:bold ;  " <?php// }; ?>
                           <?php //if ($value['emp_ot_timeattandance']['present'] == 'P/P') { ?> style="font-weight:bold ;  " <?php //}; ?>
                               <?php //if ($value['emp_ot_timeattandance']['present'] == 'P/A') { ?> style="font-weight:bold ;  " <?php //}; ?>
               <?php// if ($value['emp_ot_timeattandance']['present'] == 'A/P') { ?>  <?php //}; ?> >
               <?php
           //echo date('d',strtotime($value['emp_ot_timeattandance']['att_date']));
                //echo date('d', $value['emp_ot_timeattandance']['att_date_timestamp']);
           ?>
           </th>-->
           
              <?php
                  foreach ($arr_dates as $key => $date) {
              ?>
               <th><?php echo $date; ?></th>
              <?php
                }
              ?>
     </tr>
            
<!--     <th></th>-->
   <tr>
       <td style='width:110px !important;'>Att IN:</td>
       <?php foreach ($arr_dates as $date): ?>
        <td><?php
            $found = false;
            foreach ($val['summary'] as $value) {
                $in = $value['emp_ot_timeattandance']['att_in_time']; 
                if (isset($in)) {
                    $dateTime = new DateTime($in);
                    $in_date = intval($dateTime->format('j'));
                    $in_time = $dateTime->format('d-m-Y H:i:s');
                    if ($in_date == $date) {
                        $found = true;
                        echo $in_time;
                        break;
                    }
                }
            }
            if (!$found) {
                echo ""; // Or any other placeholder text
            }
        ?></td>
       <?php endforeach; ?>
  </tr>

    <tr>
        <td style='width:110px !important;'>Att OUT:</td>
        <?php foreach ($arr_dates as $date): ?>
        <td><?php
            $found = false;
            foreach ($val['summary'] as $value) {
                $out = $value['emp_ot_timeattandance']['att_out_time']; 
                if (isset($out)) {
                    $dateTime = new DateTime($out);
                    $out_date = intval($dateTime->format('j'));
                    $out_time = $dateTime->format('d-m-Y H:i:s');
                    if ($out_date == $date) {
                        $found = true;
                        echo $out_time;
                        break;
                    }
                }
            }
            if (!$found) {
                echo ""; // Or any other placeholder text
            }
        ?></td>
       <?php endforeach; ?>
    </tr>
    <?php
    echo '<tr>';
     echo "<td>Min before on duty OT:</td>";

      // Loop through each date
     foreach ($arr_dates as $date) {
         $found = false;
    
         // Loop through each summary value
         foreach ($val['summary'] as $val3) {
                 $out = $val3['emp_ot_timeattandance']['att_out_time']; 
           if (isset($out)) {
               $dateTime = new DateTime($out);
               $out_date = intval($dateTime->format('j'));
               if ($out_date == $date) {
                  $found = true;
                  echo '<td>';
                  echo $val3['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'];
                  echo '</td>';
                  break;
              }
            } 
        }
    
         // If no value for the current date is found, display an empty cell
        if (!$found) {
            echo "<td></td>";
        }
      }

    echo '</tr>';

    echo  '<tr>';
      echo "<td>Min after off duty OT:</td>";
        // Loop through each date
       foreach ($arr_dates as $date) {
             $found = false;
    
             // Loop through each summary value
             foreach ($val['summary'] as $val3) {
                 $out = $val3['emp_ot_timeattandance']['att_out_time']; 
                 if (isset($out)) {
                    $dateTime = new DateTime($out);
                    $out_date = intval($dateTime->format('j'));
                 if ($out_date == $date) {
                     $found = true;
                     echo '<td>';
                     echo $val3['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'];
                     echo '</td>';
                       break;
                 }
              }
             }
    
        // If no value for the current date is found, display an empty cell
           if (!$found) {
                    echo "<td></td>";
           }
        }
             
    echo '</tr>';
 echo  '<tr>';
 echo "<td><b>OT Duration:</b></td>";
        
        // Loop through each date
     foreach ($arr_dates as $date) {
        $found = false;
    
         // Loop through each summary value
        foreach($val['summary'] as $val6) {
        $out = $val6['emp_ot_timeattandance']['att_out_time']; 
        //$total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
        if (isset($out)) {
            $dateTime = new DateTime($out);
            $out_date = intval($dateTime->format('j'));
            if ($out_date == $date) {
                $found = true;
                echo '<td>';
                echo $val6['emp_ot_timeattandance']['ot_duration'];
                echo '</td>';
                // edited by sinsiya on 19-06-2024
                 $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                break;
            }
        }
       }
    
      // If no value for the current date is found, display an empty cell
      if (!$found) {
        echo "<td></td>";
      }
     }
//              foreach($val['summary'] as $val6)
//            { 
//                  $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
//           echo '<td>';
//          echo $val6['emp_ot_timeattandance']['ot_duration'];
//           echo '</td>';
//            }
 echo '</tr>';
 echo  '<tr>';
    echo "<td><b>OT Duration Hrs:</b></td>";
                // Loop through each date
       foreach ($arr_dates as $date) {
             $found = false;
    
              // Loop through each summary value
            foreach($val['summary'] as $val6) {
                   $out = $val6['emp_ot_timeattandance']['att_out_time']; 
        
                  if (isset($out)) {
                        $dateTime = new DateTime($out);
                        $out_date = intval($dateTime->format('j'));
                       if ($out_date == $date) {
                            $found = true;
                            echo '<td>';
                            echo isset($val6['emp_ot_timeattandance']['ot_duration'])?round(($val6['emp_ot_timeattandance']['ot_duration'] / 60),2):'';
                            echo '</td>';
                             break;
                        }
                   }
                }
    
          // If no value for the current date is found, display an empty cell
            if (!$found) {
                 echo "<td></td>";
             }
        }
//              foreach($val['summary'] as $val6)
//            { 
//                  
//           echo '<td>';
//          echo isset($val6['emp_ot_timeattandance']['ot_duration'])?round(($val6['emp_ot_timeattandance']['ot_duration'] / 60),2):'';
//           echo '</td>';
//            }
           echo '</tr>';
           echo '<tr>';
           echo '<td><b>Total OT Duration</b></td>';
           echo '<td colspan="32"><b>';
            if ($total!=0){echo $total .' Min  -     '.round(($total / 60),2).' Hrs.';}
           echo '</b></td>';
           echo '</tr>';
		   echo '</tr>';
           echo "</table>";
           ?>
         
</div>

  </fieldset>
                            <br>
                           
                        </div>
     <!-- /.box-body -->
<?php
                 //    }     }
                        
                    ?>
                </div>
            </div>
                        <?php }}}else { ?>
             <div class="box-body">
                            
                            <br>
                            <fieldset>
<div class="row">
   <?php $prevBranch = null; foreach($arr_leavepolicydetails_for_template as $val){
      // debug($val); //edited by sinsiya 19-03-2024
        if(count($val['summary'])>0){
                          
                       // if($val['summary']['0']['emp_ot_timeattandance']['ot_duration'] >0){  
      // $summary_item = $value['summary'][0];
        
      // $ot_duration = $summary_item['emp_ot_timeattandance']['ot_duration'];
        
        //if($ot_duration >0){
               
         if ($val['summary']['0']['Info']['branch'] != $prevBranch) {                    
       ?>
       
       <legend>Over Time  Details of <?php echo $val['summary']['0']['Info']['branch']; ?> </legend>
       <?php      
       $prevBranch = $val['summary']['0']['Info']['branch'];
        }
        foreach($val as $value){// debug($value);?>
     
               <div class="box-body" style="overflow-y: auto;">
                            
                   
                            <fieldset>
                                <div class="row">
                                    <div class="col-md-6">
                                          <legend><?php echo $value['0']['Info']['EmpName']; ?><?php echo '('. $value['0']['Info']['employee_id'].')'; ?><?php echo (isset($value['0']['empdetails']['status'])) && $value['0']['empdetails']['status'] == "2" ? ' -(Resigned)' : ''; ?></legend>
                                    </div>

                                </div>
                                <div class="modal-body">
       <?php }
$total = 0; ?>
    <table class="table table-bordered table-responsive" cellspacing="0" border="0">
     <tr><th>Date</th>
     <?php 
     //edited by sinisya 18-03-2024
     
               
                  foreach ($arr_dates as $key => $date) {
            ?>
               <th><?php echo $date; ?></th>
            <?php
                }
            ?>
            </tr>
            
<!--     <th></th> EDITED BY SINSIYA 18-03-2024-->
    <tr>
       <td style='width:110px !important;'>Att IN:</td>
       <?php foreach ($arr_dates as $date){?>
        <td><?php
            $found = false;
            foreach ($val['summary'] as $value) {
                $in = $value['emp_ot_timeattandance']['att_in_time']; 
                if (isset($in)) {
                    $dateTime = new DateTime($in);
                    $in_date = intval($dateTime->format('j'));
                    $in_time = $dateTime->format('d-m-Y H:i:s');
                    if ($in_date == $date) {
                        $found = true;
                        echo $in_time;
                        break;
                    }
                }
            }
            if (!$found) {
                echo ""; // Or any other placeholder text
            }
        ?></td>
        <?php } ?>
  </tr>
          <tr>
        <td style='width:110px !important;'>Att OUT:</td>
        <?php foreach ($arr_dates as $date){?>
        <td><?php
            $found = false;
            foreach ($val['summary'] as $value) {
                $out = $value['emp_ot_timeattandance']['att_out_time']; 
                if (isset($out)) {
                    $dateTime = new DateTime($out);
                    $out_date = intval($dateTime->format('j'));
                    $out_time = $dateTime->format('d-m-Y H:i:s');
                    if ($out_date == $date) {
                        $found = true;
                        echo $out_time;
                        break;
                    }
                }
            }
            if (!$found) {
                echo ""; // Or any other placeholder text
            }
        ?></td>
        <?php } ?>
    </tr>
          <?php
    echo '<tr>';
     echo "<td>Min before on duty OT:</td>";

      // Loop through each date
     foreach ($arr_dates as $date) {
         $found = false;
    
         // Loop through each summary value
         foreach ($val['summary'] as $val3) {
                 $out = $val3['emp_ot_timeattandance']['att_out_time']; 
           if (isset($out)) {
               $dateTime = new DateTime($out);
               $out_date = intval($dateTime->format('j'));
               if ($out_date == $date) {
                  $found = true;
                  echo '<td>';
                  echo $val3['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'];
                  echo '</td>';
                  break;
              }
            } 
        }
    
         // If no value for the current date is found, display an empty cell
        if (!$found) {
            echo "<td></td>";
        }
      }

    echo '</tr>';

    echo  '<tr>';
      echo "<td>Min after off duty OT:</td>";
        // Loop through each date
       foreach ($arr_dates as $date) {
             $found = false;
    
             // Loop through each summary value
             foreach ($val['summary'] as $val3) {
                 $out = $val3['emp_ot_timeattandance']['att_out_time']; 
                 if (isset($out)) {
                    $dateTime = new DateTime($out);
                    $out_date = intval($dateTime->format('j'));
                 if ($out_date == $date) {
                     $found = true;
                     echo '<td>';
                     echo $val3['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'];
                     echo '</td>';
                       break;
                 }
              }
             }
    
        // If no value for the current date is found, display an empty cell
           if (!$found) {
                    echo "<td></td>";
           }
        }
             
    echo '</tr>';
 echo  '<tr>';
 echo "<td><b>OT Duration:</b></td>";
        
        // Loop through each date
     foreach ($arr_dates as $date) {
        $found = false;
    
         // Loop through each summary value
        foreach($val['summary'] as $val6) {
        $out = $val6['emp_ot_timeattandance']['att_out_time']; 
        
        if (isset($out)) {
            $dateTime = new DateTime($out);
            $out_date = intval($dateTime->format('j'));
            if ($out_date == $date) {
                $found = true;
                echo '<td>';
                echo $val6['emp_ot_timeattandance']['ot_duration'];
                echo '</td>';
                $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                break;
            }
        }
       }
    
      // If no value for the current date is found, display an empty cell
      if (!$found) {
        echo "<td></td>";
      }
     }
//              foreach($val['summary'] as $val6)
//            { 
//                  $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
//           echo '<td>';
//          echo $val6['emp_ot_timeattandance']['ot_duration'];
//           echo '</td>';
//            }
 echo '</tr>';
 echo  '<tr>';
    echo "<td><b>OT Duration Hrs:</b></td>";
                // Loop through each date
       foreach ($arr_dates as $date) {
             $found = false;
    
              // Loop through each summary value
            foreach($val['summary'] as $val6) {
                   $out = $val6['emp_ot_timeattandance']['att_out_time']; 
        
                  if (isset($out)) {
                        $dateTime = new DateTime($out);
                        $out_date = intval($dateTime->format('j'));
                       if ($out_date == $date) {
                            $found = true;
                            echo '<td>';
                            echo isset($val6['emp_ot_timeattandance']['ot_duration'])?round(($val6['emp_ot_timeattandance']['ot_duration'] / 60),2):'';
                            echo '</td>';
                             break;
                        }
                   }
                }
    
          // If no value for the current date is found, display an empty cell
            if (!$found) {
                 echo "<td></td>";
             }
        }
//              foreach($val['summary'] as $val6)
//            { 
//                  
//           echo '<td>';
//          echo isset($val6['emp_ot_timeattandance']['ot_duration'])?round(($val6['emp_ot_timeattandance']['ot_duration'] / 60),2):'';
//           echo '</td>';
//            }
           echo '</tr>';
           echo '<tr>';
           echo '<td><b>Total OT Duration</b></td>';
           echo '<td colspan="32"><b>';
            if ($total!=0){ echo $total .' Min  -     '.round(($total / 60),2).' Hrs.';}
           echo '</b></td>';
           echo '</tr>';
		   echo '</tr>';
          // echo "</table>";
           ?>
    </table> 
</div>

  </fieldset>
                   </div>
             </div>
             </div>
                        <?php   } } } ?>
                 
                    <?php   
                    } else { ?><!--<div style="text-align:center; font-weight: bold;"><legend>No data found</legend></div>-->
                        <div style="font-size: 17px;text-align:left;">
                        No data found</div> 
                                       <?php } ?>
        </div>  
    </div>
    </div><!-- comment -->
    </div>
<?php } ?>