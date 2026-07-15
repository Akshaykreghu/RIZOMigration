<?php

/* 
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

?>
 <section class="content">
<div title="Attendance Register" data-options="iconCls:'icon-save'" style="overflow:auto;padding:10px;" class="table table-responsive">
<table  class="table table-bordered"  id="LeaveDetailsReports">
    <thead>
  <th>Si No.</th>
  <th>Employee NAME</th>

  <?php 
if(isset($employee_attendance) && count($employee_attendance) > 0)
{
  $date = current($employee_attendance);
  
      foreach($date as $vals)
      {
       
     
  ?>
  <th><?php  echo substr($vals['emp_detail_timeattandance']['att_date'],8,2) ; ?></th>
  <?php
   }
}
  ?>
 

</thead>
<tbody>

 <?php
 $i=0;
 foreach($employee_attendance as $val)
 {
	  
 $i+=1;
 ?>
<div title="expand" data-options="iconCls:'icon-save'">
 <tr >
 <td><?php echo $i ?></td>
<td><?php echo $val['0']['empdetails']['first_name'].' '.$val['0']['empdetails']['last_name'] ?></td>

<?php 
foreach($val as $value)
 {

 ?>
<td <?php if($value['emp_detail_timeattandance']['weekoff']) { ?> style="color:black ;font-weight:bold ;  " <?php } ; ?> <?php if($value['emp_detail_timeattandance']['present'] == 'A/A') { ?> style="color:red ;font-weight:bold ;  " <?php } ; ?><?php if($value['emp_detail_timeattandance']['present'] == '') { ?> style="background:#0AC5B6 ;font-weight:bold ;  " <?php } ; ?><?php if($value['emp_detail_timeattandance']['present'] == 'P/P') { ?> style="color:green ;font-weight:bold ;  " <?php } ; ?><?php if($value['emp_detail_timeattandance']['present'] == 'P/A') { ?> style="color:red ;font-weight:bold ;  " <?php } ; ?><?php if($value['emp_detail_timeattandance']['present'] == 'A/P') { ?> style="color:red ; " <?php } ; ?> ><?php echo $value['emp_detail_timeattandance']['present'].' '.$value['emp_detail_timeattandance']['holiday'].' '.$value['emp_detail_timeattandance']['leaves'].' '.$value['emp_detail_timeattandance']['weekoff'].' '.$value['emp_detail_timeattandance']['others']; ?></td>
 <?php
 }
 ?>
 </tr>
</div>
 <tr>
     <td></td>
     <td style="text-align: center ; ">Att In</td>

 <?php 
foreach($val as $value1)
 {
 
 ?>
<td><?php echo $value1['emp_detail_timeattandance']['att_in_time'] ?></td>
 <?php
 }
 ?>
</tr>
 <tr>
     <td></td>
<td style="text-align: center ; ">Att Out</td>

 <?php 
foreach($val as $out_time)
 {
 
 ?>
<td><?php echo $out_time['emp_detail_timeattandance']['att_out_time'] ?></td>
 <?php
 }
 ?>
</tr>
 <tr>
     <td></td>
     <td style="text-align: center ; ">Duration</td>
 <?php 
foreach($val as $duration)
 {
 
 ?>
 <td  <?php    if($duration['emp_detail_timeattandance']['duration'] >= $duration['wd']['minuts_calc_perday']){ ?> style="background:#34F593 ; " <?php } elseif($duration['emp_detail_timeattandance']['duration'] < $duration['wd']['minuts_calc_perday'] & $duration['emp_detail_timeattandance']['duration']  !=''){ ?> style="background:#FF967E ; " <?php }  ?> ><?php echo $duration['emp_detail_timeattandance']['duration'] ?></td>
 <?php
 }
 ?>
</tr>
 <?php
 }
 ?>
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
