<style>
    td,th,table
    {
        border-style: double;
    }
	table.table-bordered th:last-child, table.table-bordered td:last-child{
        border-right-width:1px;
    }
</style>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend class="text-center">Clock Wise Attendance  Report</legend>
        <div class="row">
            <div class="col-md-12">
                 <?php
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $branch=> $employees) {
                        //debug(current(current($employees)));
                        $arr_e = $employees['summary'];
                        $i += 1;
                        ?>
                <div class="">
                   
                    <h2><?php echo "Clock Wise Attendance Reports of ".$employees['leavepolicyname']; ?></h2>
                    
                    <table class="table table-bordered" >
                        <thead>
                            <th>Sl No</th>
                            <th>Employee Name</th>
                            <th>Employee ID</th>
                            <th>Branch</th>
                            <th>Designation</th>
                            <?php foreach($arr_dates as $val){ ?>
                                <th><?php echo $val; ?></th>
                            <?php  } ?>
                            <th>Total Hours</th>
                            <th>No of Working Day</th>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            <?php if(empty($arr_e)){ ?><td colspan="<?php echo count($arr_dates) + 7  ; ?>">No Record found under this branch</td><?php } ?>
                            <?php foreach($arr_e as $val) { ?>
                            <?php $i++; ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $val['site_attendance_register']['emp_name']; ?></td>
                                <td><?php echo $val['emp_proff']['emp_company_id']; ?></td>
                                <td><?php echo $val['branches']['branch_name']; ?></td>
                                <td><?php echo $val['designation']['desig_name']; ?></td>
                                <?php
                                    $tot = 0;
                                    $workings = 0;
                                ?>
                                 <?php foreach($arr_dates as $key=> $date)
                                            {
                                            ?>       
                                            <td> 
                                                <?php 
                                                $newIndex='FIELD'.($key+1);
                                                $tot += $val['site_attendance_register'][$newIndex];
                                                if($val['site_attendance_register'][$newIndex] > 0){
                                                    $workings++;
                                                }
                                               echo (round($val['site_attendance_register'][$newIndex])==0)?'':round($val['site_attendance_register'][$newIndex],2); 
                                                ?></td>                                          
                                            <?php } 
                                            $tot = round($tot,2);?>
                                <td><?php echo $tot; ?></td>
                                <td><?php echo $workings; ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } 
                  if (empty($arr_leavepolicydetails_for_template)){?> <!-- /.box-body -->
                    <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
             <?php } ?> 
            </div>
        </div>
        <div class="row">

    </div>
        
    </div>
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

    

<?php } ?>
