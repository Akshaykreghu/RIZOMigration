<section class="content">
    <div title="Attendance Register" data-options="iconCls:'icon-save'" style="overflow:auto;padding:10px;" class="table table-responsive">
        <!--<input type="hidden" value="<?php echo $monthdd; ?>" >-->
        Detailed Logs
        <table  class="table table-bordered" >
            <thead>
<!--            <th>Sl No.</th>-->
            <th>Employee Name</th>

<?php
if (isset($employee_attendance) && count($employee_attendance) > 0) {
    $date = current($employee_attendance);
    foreach ($employee_attendance as $vals) {
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
//            foreach ($employee_attendance as $val) {
//                    $i+=1;
//                    if(!isset($val['0']['empdetails']['first_name']))
//                        continue;
//                    ?>
                    <div title="expand" data-options="iconCls:'icon-save'">
                        <tr >
<!--                            <td><?php //echo $i ?></td>-->
                            <td><?php  echo $employee_attendance['0']['empdetails']['first_name'] . ' ' . $employee_attendance['0']['empdetails']['last_name'];  ?></td>

                        <?php
                        foreach ($employee_attendance as $duration) {
                            ?>
                            <td   ><?php echo $duration['emp_site_detail_timeattandance']['present'] ?></td>
                            <?php
                        }
                        ?>
                        </tr>
                        <tr >
<!--                            <td><?php //echo $i ?></td>-->
                            <td><?php  echo 'Att In Time';  ?></td>

                        <?php
                        foreach ($employee_attendance as $duration) {
                            ?>
                            <td   ><?php echo $duration['emp_site_detail_timeattandance']['att_in_time'] ?></td>
                            <?php
                        }
                        ?>
                        </tr>
                        <tr >
<!--                            <td><?php //echo $i ?></td>-->
                            <td><?php  echo 'Att Out Time';  ?></td>

                        <?php
                        foreach ($employee_attendance as $duration) {
                            ?>
                            <td   ><?php echo $duration['emp_site_detail_timeattandance']['att_out_time'] ?></td>
                            <?php
                        }
                        ?>
                        </tr>
                        <tr >
<!--                            <td><?php //echo $i ?></td>-->
                            <td><?php  echo 'Duration';  ?></td>

                        <?php
                        foreach ($employee_attendance as $duration) {
                            ?>
                            <td   ><?php echo round($duration['emp_site_detail_timeattandance']['duration']); ?></td>
                            <?php
                        }
                        ?>
                        </tr>
                        
                    </div>
                    
                        <?php
//                }
                ?>
            </tbody>


        </table>
    </div>
</section>