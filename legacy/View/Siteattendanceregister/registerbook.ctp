<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<section class="content">
    <div title="Attendance Register" data-options="iconCls:'icon-save'" style="overflow:auto;padding:10px;" class="table table-responsive">
        <input type="hidden" value="<?php echo $monthdd; ?>" >
          <?php $arr_site= isset($arr_sites)?count($arr_sites):1;
          $arr_site_list= isset($arr_site_lists)?count($arr_site_lists):1;
            if($arr_site >1){ ?>
        <table  class="table table-bordered"  id="LeaveDetailsReports">
            <thead>
            <th>Sl No.</th>
            <th>Employee Name</th>
            <th>Site Name</th>
            <th>Expand</th>
<?php
if (isset($employee_attendance) && count($employee_attendance) > 0) {
    $date = current($employee_attendance);
    $i = 0;
    foreach ($date as $vals) {
       if($arr_sites['0']['emp_site_detail_timeattandance']['site_fkey'] != $vals['emp_site_detail_timeattandance']['site_fkey'])
         continue;
        ?>
         <th><?php echo substr($vals['emp_site_detail_timeattandance']['att_date'], 8, 2); ?></th>
       <?php } 
             }
            ?>
            </thead>
            <tbody>
            <?php
             $i = 0;
              $j = 0;
          foreach ($arr_sites as $arr) {
            foreach ($employee_attendance as $val) {
                 if($arr['emp_site_detail_timeattandance']['site_fkey'] == $val[$j]['emp_site_detail_timeattandance']['site_fkey']){
                     $site = $val[$j]['site']['site_name'];
                     $sitekey = $val[$j]['emp_site_detail_timeattandance']['site_fkey'];
                 }
                    $i+=1;
                    if(!isset($val['0']['empdetails']['first_name']))
                        continue;
                     ?>
                    <div title="expand" data-options="iconCls:'icon-save'">
                        <tr >
                            <td><?php echo $i ?></td>
                            <!-- //edited by megha on 22_06_19 Site name removed-->
                            <!-- <td><?php //if($site_wise == 1){ echo $val['0']['site']['site_name']; } else { echo $val['0']['empdetails']['first_name'] . ' ' . $val['0']['empdetails']['last_name']; } ?></td>-->
                            <td><?php  echo $val['0']['empdetails']['first_name'] . ' ' . $val['0']['empdetails']['last_name'];  ?></td>
                            <td><?php  echo $site;  ?></td>
                            <td><button class="btn btn-default" onclick="opendetails(<?php echo $val['0']['emp_site_detail_timeattandance']['emp_pkey']; ?>,<?php echo $sitekey; ?>);"><li class="fa fa-plus"></li></button></td>
                        <?php foreach ($val as $duration) {
                             if($arr['emp_site_detail_timeattandance']['site_fkey'] == $duration['emp_site_detail_timeattandance']['site_fkey']){
                             $j+=1;  ?>
                            <td   ><?php echo $duration['emp_site_detail_timeattandance']['duration'] ?></td>
                     <?php } } ?>
                        </tr>
                    </div>
            <?php } } ?>
          </tbody>
        </table>
          <?php } else {?>
         <table  class="table table-bordered"  id="LeaveDetailsReports">
            <thead>
            <th>Sl No.</th>
            <th>Employee Name</th>
            <th>Expand</th>

<?php
if (isset($employee_attendance) && count($employee_attendance) > 0) {
    $date = current($employee_attendance);

    foreach ($date as $vals) {
        
        ?>
                    <th><?php echo substr($vals['emp_site_detail_timeattandance']['att_date'], 8, 2); ?></th>
                    <?php
                }
            }
            ?>


            </thead>
            <tbody>

            <?php
            $i = 0;
            foreach ($employee_attendance as $val) {
                    $i+=1;
                    if(!isset($val['0']['empdetails']['first_name']))
                        continue;
                    ?>
                    <div title="expand" data-options="iconCls:'icon-save'">
                        <tr >
                            <td><?php echo $i ?></td>
                            <!-- //edited by megha on 22_06_19 Site name removed-->
                            <!-- <td><?php //if($site_wise == 1){ echo $val['0']['site']['site_name']; } else { echo $val['0']['empdetails']['first_name'] . ' ' . $val['0']['empdetails']['last_name']; } ?></td>-->
                            <td><?php  echo $val['0']['empdetails']['first_name'] . ' ' . $val['0']['empdetails']['last_name'];  ?></td>
                            <td><button class="btn btn-default" onclick="opendetails(<?php echo $val['0']['emp_site_detail_timeattandance']['emp_pkey']; ?>,<?php echo $val['0']['emp_site_detail_timeattandance']['site_fkey']; ?>);"><li class="fa fa-plus"></li></button></td>

                        <?php
                        foreach ($val as $duration) {
                            ?>
                            <td><?php //edited by sinsiya on 15-09-2025
                             if($company_code === 'ABSG'){ echo $duration['emp_site_detail_timeattandance']['duration']; }else{ echo round($duration['emp_site_detail_timeattandance']['duration']);
                            }?></td>
                            
                            <?php
                        }
                        ?>
                        </tr>
                    </div>
                    
                        <?php
                }
                ?>
            </tbody>


        </table>
          <?php } ?>
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
        animate: true
    });

    function opendetails(emp_fkey,site_fkey){
        var emp_fkey  = emp_fkey;
        var month = $('#filterby_month').val();
        var branch = site_fkey; // $('#filterby_branch').val();
        showLargeModalForm(livesite + 'Siteattendanceregister/getmodal/' +emp_fkey+ '/' +month + '/' +branch);
    }

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
            "pageNumber": true,
            "lengthChange": true,
            "searching": true,
            "ordering": false,
            "info": true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'print',
                    messageTop: 'My Payroll Master  Site Attendance Register.',
                    messageBottom: null,
                    title: 'My Payroll Master - Attendance Register'
                },
                {
                    extend: 'excel',
                    messageTop: 'My Payroll Master  Site Attendance Register.',
                    messageBottom: null,
                    title: 'My Payroll Master  - Attendance Register'
                }
            ],
            "autoWidth": false,
            "lengthMenu": [[12, 24, 50, -1], [12, 24, 50, "All"]]
        });

        $('.buttons-print').ready(function () {
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');
            ;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
        ;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');

        // Event listener to the two range filtering inputs to redraw on input
        $('#leaverequests-emp-filter, #leaverequests-month-filter').change(function () {
            empleaverequeststable.search(this.value).draw();
        });
    });

</script>
