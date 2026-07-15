<?php

/* 
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

?>
<section class="content"> 
    <div title="Attendance Register" data-options="iconCls:'icon-save'" style="overflow:auto;padding:10px;" class="table table-responsive">
    <?php  
    if(empty($employee_attendance)){?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available
    </div>
<?php }else{?>

    <table  class="table table-bordered"  id="LeaveDetailsReports">
         <thead>
            <th style="width: 1px;">Sl No.</th>
            <th style="width: 175px;">Site Name</th>

  <?php 
//                          debug($employee_attendance);
//                          echo $employee_attendance['210']['0']['emp_detail_timeattandance']['att_date'];
  $i=1;         
  if(isset($employee_attendance) && count($employee_attendance) > 0)
    {
       foreach($arr_month as $dates)
           {?>
            <th><?php  echo date('d',strtotime($dates['emp_detail_timeattandance']['att_date'])) ?></th>      
     <?php  }
    } ?>
 </thead>
         <tbody>
            <?php
             $i=0;
             foreach($arr_month as $dates){ ?>
                 <!--<td><?php //  echo date('d',strtotime($dates['emp_detail_timeattandance']['att_date'])) ?></td>-->
           <?php  }
             foreach($employee_attendance as $val)
             {
//                 debug($val);
                 $parent = reset($val);
//                                  debug($parent['emp_details']['0']['emp_details']['first_name']); 
                 $i+=1; ?>
                   <div title="expand" data-options="iconCls:'icon-save'">
                        <tr>
                            <td><?php echo $i;?></td>
                        <!--<td><?php // echo date('d',srtotime($arr_attendance['0']['emp_detail_timeattandance']['att_date'])) ?></td>-->
                        <td><b><?php echo isset($parent['emp_details']['0']['emp_details']['first_name']) ?$parent['emp_details']['0']['emp_details']['first_name']:''; ?> &nbsp;<?php echo isset($parent['emp_details']['0']['emp_details']['last_name']) ?$parent['emp_details']['0']['emp_details']['last_name']:''; ?></b></td>
                     <?php 
                          foreach($employee_attendance as $vals)
                              {
                                foreach($vals as $key => $value)
                                    {
//                                       debug($key);
//                                       debug($value);
                                         foreach($value as $key => $valuess)
                                             {
//                                             debug($key);
                                             if($key == 'emp_details'){
                                                 continue;
                                             }
                                                if(empty($valuess))
                                                    {?>
                                                     <td><?php echo ("0");?></td>
                                                     <?php }else  
                                                     {
                                                       $present = isset($valuess['0']['emp_detail_timeattandance']['present']) ? $valuess['0']['emp_detail_timeattandance']['present'] :'';
                                                       $weekoff = isset($valuess['0']['emp_detail_timeattandance']['weekoff']) ? $valuess['0']['emp_detail_timeattandance']['weekoff'] : '';
                                                       $holiday = isset($valuess['0']['emp_detail_timeattandance']['holiday']) ? $valuess['0']['emp_detail_timeattandance']['holiday'] : '';
                                                       $others = isset($valuess['0']['emp_detail_timeattandance']['others']) ? $valuess['0']['emp_detail_timeattandance']['others'] : '';
                                                       $leaves  = isset($valuess['0']['emp_detail_timeattandance']['leaves']) ? $valuess['0']['emp_detail_timeattandance']['leaves'] : '';
//                                                               debug($valuess); ?>
                                                           <td <?php if($weekoff)  
                                                               { ?> style="color:black ;font-weight:bold ;  " <?php }  ?> <?php if($present == 'A/A') 
                                                                   { ?> style="color:red ;font-weight:bold ;  " <?php }  ?><?php if($present == '') 
                                                                       { ?> style="background:#0AC5B6 ;font-weight:bold ; " <?php }  ?><?php if($present == 'P/P') 
                                                                           { ?> style="color:green ;font-weight:bold ;  " <?php } ?><?php if($present == 'P/A') 
                                                                               { ?> style="color:red ;font-weight:bold ;  " <?php }  ?><?php if($present == 'A/P') 
                                                                                   { ?> style="color:red ; " <?php }  ?> ><?php echo $present.' '.$holiday.' '.$leaves.' '.$weekoff.' '.$others; ?></td>
                                                           <?php
                                                         
                                                    }
                                                    
                                             } ?></tr>
                                        </div>
                                     <?php ?>
                                            <tr>
                                                <td></td>
                                                <td style="text-align: center ; ">Att In</td>
                                                <?php 
//                                    debug($value);
                                               foreach($value as $key => $valuess)
//                                                    debug ($valuess);
                                                 {     if($key == 'emp_details'){
                                                 continue;
                                             }
//                                                 ?>
                                                   <td><?php echo isset($valuess['0']['emp_detail_timeattandance']['att_in_time']) ? $valuess['0']['emp_detail_timeattandance']['att_in_time'] : '' ;?></td>
                                                      <?php
                                                 } ?>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="text-align: center ; ">Att Out</td>
                                                 <?php 
                                               foreach($value as $key => $valuess)
                                             
                                                {       if($key == 'emp_details'){
                                                 continue;
                                             }  ?>
                                                    <td><?php echo isset($valuess['0']['emp_detail_timeattandance']['att_out_time']) ? $valuess['0']['emp_detail_timeattandance']['att_out_time'] : ''; ?></td>
                                                    <?php
                                                } ?>
                                            </tr>
                                            <tr>
                                                     <td></td>
                                                     <td style="text-align: center ; ">Duration</td>
                                                 <?php 
                                                foreach($value as $key => $valuess)
//                                                    
                                                 { if($key == 'emp_details'){
                                                 continue;
                                             } 
//                                             debug ($valuess)  ;  ?>
                                                      <td  <?php 
                                                                    $duration  = isset($valuess['0']['emp_detail_timeattandance']['duration']) ? $valuess['0']['emp_detail_timeattandance']['duration'] : '';
                                                                    $minutesperday = isset($valuess['0']['wd']['minuts_calc_perday']) ? $valuess['0']['wd']['minuts_calc_perday'] : '';
//                                                                    debug($duration);
                                                                    if($duration == ''){
                                                                       ?> style="background:#ffffff;" 
                                                                    <?php 
                                                                    }
                                                                    if($duration >= $minutesperday)
                                                                    { ?> style="background:#34F593 ; " 
                                                                    <?php } 
                                                                    elseif($duration < $minutesperday & $duration  !='')
                                                                        { ?> style="background:#FF967E ; " 
                                                                  <?php }  ?> >
                                                                   <?php echo isset($duration) ? $duration: '';?></td>
                                                        <?php
                                                 }  ?>
                                            </tr>
                             <?php }     
                            }
                       } ?>
        </tbody>
    </table>
   
</div>
   <!--  <div id="aa" class="easyui-accordion" style="width:300px;height:200px;">
    <div title="Title1" data-options="iconCls:'icon-save'" style="overflow:auto;padding:10px;">
        <h3 style="color:#0099FF;">Accordion for jQuery</h3>
        <p>Accordion is a part of easyui framework for jQuery. 
        It lets you define your accordion component on web page more easily.</p>
    </div>
    <div title="Title2" data-options="iconCls:'icon-reload',selected:true" style="padding:10px;">
        content2
    </div>
    <div title="Title3">
        content3
    </div>
</div> -->
   <div class="col-md-12" style="margin-top: -9px;">
         <div class="col-md-1" style="background: green;color: white;text-align: center;width: 60px;">P</div>
         <div class="col-md-1" style="font-weight: 600;"> - &nbsp;Present</div>
         <div class="col-md-1" style="background: yellow;color: black;text-align: center;width: 60px;margin-left: 23px;">WO</div>
         <div class="col-md-1" style="font-weight: 600;"> -&nbsp; Week Off</div>
         <div class="col-md-1" style="background: red;color: white;text-align: center;width: 60px;margin-left: 33px;">A</div>
         <div class="col-md-1" style="font-weight: 600;"> - &nbsp;Absent</div>
         <div class="col-md-1" style="background: #34F593;color: black;text-align: center;width: 60px;margin-left: 31px;">AWT</div>
         <div class="col-md-2" style="font-weight: 600;width: 226px;"> -&nbsp; Above Minimum Working Time</div>
         <div class="col-md-1" style="background: #FF967E;color: black;text-align: center;width: 60px;margin-left: 52px;">BMW</div>
         <div class="col-md-2" style="font-weight: 600;width: 226px;"> -&nbsp; Below Minimum Working Time</div>
        </div> 
<?php } ?>
 </section>

<script type="text/javascript">
    $('#aa').accordion({
    animate:true
});

     $(document).ready(function () {
        $('.tabset0').pwstabs({
            effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: true, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false                    // Right to left support: true/ false
        });
        /*$('#togglechartweek').on('click', function () {
            $("weekChartRow").show();
            $("monthChartRow").hide();

        })
        $('#togglechartmonth').on('click', function () {
            $("weekChartRow").hide();
            $("monthChartRow").show();
        })*/
        $('#LeaveDetailsReports').DataTable({
            "paging": true,
            "pageNumber":true,
            "lengthChange": true,
            "searching": true,
            "ordering": false,
            "info": true,
            "autoWidth": false,
            "lengthMenu": [[12, 24, 50, -1], [12, 24, 50, "All"]]
        });
 
       
        // Event listener to the two range filtering inputs to redraw on input
        $('#leaverequests-emp-filter, #leaverequests-month-filter').change( function() {
            empleaverequeststable.search( this.value ).draw();
        });
    });

    </script>
