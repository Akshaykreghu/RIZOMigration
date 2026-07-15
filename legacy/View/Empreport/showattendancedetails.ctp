<?php $count = 0;
foreach ($arr_timings as $type) {
    $count+= count($type);
  
}  $i= $count;?>
<style>

<?php if($i < 11) { ?>
.scrollbar
{
        margin-left: 28px;
        float: right;
      
        width: 588px;
	//background: #F5F5F5;
	overflow-y: scroll;
	margin-bottom: 25px;
}
<?php } else { ?>
.scrollbar
{
        margin-left: 28px;
        float: right;
        height: 434px;
        width: 588px;
	//background: #F5F5F5;
	overflow-y: scroll;
	margin-bottom: 25px;
}
<?php } ?>
.force-overflow
{
	min-height: 450px;
}

#wrapper
{
	text-align: center;
	width: 500px;
	margin: auto;
}



#style-4::-webkit-scrollbar
{
	width: 10px;
	background-color: #F5F5F5;
}

#style-4::-webkit-scrollbar-thumb
{
	background-color: #00659f;
        border: 2px solid #00659f;
}


    .scrollit {
    overflow:scroll;
    height:455px;
}
</style>

<?php if($arr_timings != null) {?>
<div class="box box-primary" style="height: 537px;">
    <div class="box box-body" style="border: white;">
<h2 class="page-header" style="text-align:center;font-size: 28px;">Attendance Details</h2>
<div class="table table-responsive scrollbar" id="style-4">
<table class="table no-margin"  id="showpunchingdetails"  name="showpunchingdetails">
    <thead>
    <th>Date</th>
    <th>Time</th>
    <th>Check-In/Out</th>
    <th>Location</th>
    
  
   
</thead>
<tbody>
    <?php 
   // debug($arr_timings);
    $empname = $arr_empdata['0']['employee_info']['EmpName'];
    $id = $arr_empdata['0']['employee_info']['employee_id'];
    $branch = $arr_empdata['0']['employee_info']['branch'];
    $arr   = end($arr_timings);
    $end =  $arr['device_attandance']['LOGDATE'];
    $enddate = date("Y/m/d",strtotime($end));
    $start = $arr_timings['0']['device_attandance']['LOGDATE'];
    $startdate = date("Y/m/d",strtotime($start));
    foreach ($arr_timings as $val) { 
      $in =  $val['device_attandance']['LOGDATE'];
      $time = date("H:i:s",  strtotime($in));
      $date = date("Y/m/d",strtotime($in));
      if($val['device_attandance']['C3'] == ''){
          $location = $branch;
      }
      else{
          $location = $val['device_attandance']['C3'];
      }
//      $location = isset($val['device_attandance']['C3']) && $val['device_attandance']['C3'] == '' ? 
     
       echo'<tr style="border: 1px">
                
                  <td style="">' . $date . '</td>
                      
                  <td style="">' . $time . '</td>
                  <td style="">' . $val['device_attandance']['C1'] . '</td>
                  <td style="">' . $location . '</td>
               
                 
               
     </tr>'; ?>

    <?php } ?>
   
        
        
</tbody>

</table>
</div>
    
</div>
</div>
<?php }
 else {?>
    <div class="box box-primary" style="height: 537px;">
    <div class="box box-body" style="border: white;height: 534px;">
     <h1 class="page-header" style="text-align:center">Attendance Details</h1>
     <div class="col-md-12" style="background: #00659f;color: white;text-align: center; font-size: 17px; margin-top: 34px;"> <label> <span >No Data Available !!!</span></label></div>
    </div>
    </div>
 <?php } ?>
<script type="text/javascript">
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
        $('#showpunchingdetails').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            dom: 'Bfrtip',
            buttons: [
               
                {
                     extend: 'excel',
                     messageTop: '<?php  echo ("From : "); ?><?php  echo $startdate; ?> <?php  echo (" - To : "); ?> <?php  echo $enddate; ?>',
                     messageBottom: null,
                     title: ' Check-In/Out logs - <?php  echo $empname; ?> - <?php  echo $id; ?>'
                }
            ],
            "info": true,
            "autoWidth": false,
            "initComplete": function () {
                //actions
            },
//             "createdRow": function ( row, data, index ) {
//                if ( data[5].replace(/[\$,]/g, '') * 1 > 150000 ) {
//                    $('td', row).eq(5).addClass('highlight');
//                }
//            },
            "scrollCollapse": true,
        });
 
         $('.buttons-print').ready(function(){
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-primary').addClass('btn');;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-primary').addClass('btn');
       
        // Event listener to the two range filtering inputs to redraw on input
//        $('#leaverequests-emp-filter, #leaverequests-month-filter').change( function() {
//            empleaverequeststable.search( this.value ).draw();
//        });

    });

    </script>