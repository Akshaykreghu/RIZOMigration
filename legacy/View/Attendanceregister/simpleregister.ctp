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
            <!--EDITED BY SINSIYA ON 13-06-2025-->
            <th>Employee ID</th>
            <th>User ID</th>
            <th>Employee NAME</th>
             

<?php
if (isset($employee_attendance) && count($employee_attendance) > 0) {
    $date = current($employee_attendance);

    foreach ($date as $vals) {
        ?>
                    <th><?php echo substr($vals['emp_detail_timeattandance']['att_date'], 8, 2); ?></th>
                    <?php
                }
            }
            ?>


            </thead>
            <tbody>

            <?php
            $i = 0;
            foreach ($employee_attendance as $val) {
                if (count($val) < 10) {
                    
                } else {
                    $i+=1;
                    ?>
                    <div title="expand" data-options="iconCls:'icon-save'">
                        <tr >
                            <td><?php echo $i ?></td>
                              <!--EDITED BY SINSIYA ON 13-06-2025--> 
                            <td><?php echo $val['0']['empinfo']['employee_id'] ?></td>
                            <td><?php echo $val['0']['emp']['emp_company_id'] ?></td>
                            <td><?php echo $val['0']['empdetails']['first_name'] . ' ' . $val['0']['empdetails']['last_name'] ?></td>
                          
                           

                        <?php
                        foreach ($val as $value) {
                            ?>
                                <td <?php if ($value['emp_detail_timeattandance']['weekoff']) { ?> style="color:black ;font-weight:bold ;  " <?php }; ?> <?php if ($value['emp_detail_timeattandance']['present'] == 'A/A') { ?> style="color:red ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_detail_timeattandance']['present'] == '') { ?> style="background:#0AC5B6 ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_detail_timeattandance']['present'] == 'P/P') { ?> style="color:green ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_detail_timeattandance']['present'] == 'P/A') { ?> style="color:red ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_detail_timeattandance']['present'] == 'A/P') { ?> style="color:red ; " <?php }; ?> ><?php echo $value['emp_detail_timeattandance']['present'] . ' ' . $value['emp_detail_timeattandance']['holiday'] . ' ' . $value['emp_detail_timeattandance']['leaves'] . ' ' . $value['emp_detail_timeattandance']['weekoff'] . ' ' . $value['emp_detail_timeattandance']['others']; ?></td>
            <?php
        }
        ?>
                        </tr>
                    </div>
                        <?php
                    }
                }
                ?>
            </tbody>


        </table>
    </div>
</section>

<script type="text/javascript">
    $('#aa').accordion({
        animate: true
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
            "pageNumber": true,
            "lengthChange": true,
            "searching": true,
            "ordering": false,
            "info": true,
            dom: 'Bfrtip',
            buttons: [
                {
                     extend: 'print',
                     messageTop: 'MypayrollMater Employees Attendance Report.',
                     messageBottom: null,
                     title: 'MypayrollMater - Attendance Export'
                },
                {
                     extend: 'pdf',
                     messageTop: 'MypayrollMater Employees Attendance Report.',
                     messageBottom: null,
                     title: 'MypayrollMater - Attendance Export'
                },
                {
                     extend: 'excel',
                     messageTop: 'MypayrollMater Employees Attendance Report.',
                     messageBottom: null,
                     title: 'MypayrollMater - Attendance Export'
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
