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
    .col-md-6 {
        width: 50.33%;
        float: left;
    }
    .col-md-12 {
        width: 100%;
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
     
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$total = 0;
$cnt = count($attendances);    
?>
    <table class="table table-bordered table-responsive" cellspacing="0" border="0">
        <tr><th></th>
            <?php
            foreach ($attendances as $value) {
                ?>
                <td <?php if ($value['emp_ot_timeattandance']['weekoff']) { ?> style="background:greenyellow ;color:black ;font-weight:bold ;  " <?php }; ?> <?php if ($value['emp_ot_timeattandance']['holiday']) { ?> style="background:green ;color:black ;font-weight:bold ;  " <?php }; ?> <?php if ($value['emp_ot_timeattandance']['present'] == 'A/A') { ?> style="color:red ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_ot_timeattandance']['present'] == '') { ?> style="background:#0AC5B6 ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_ot_timeattandance']['present'] == 'P/P') { ?> style="color:green ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_ot_timeattandance']['present'] == 'P/A') { ?> style="color:red ;font-weight:bold ;  " <?php }; ?><?php if ($value['emp_ot_timeattandance']['present'] == 'A/P') { ?> style="color:red ; " <?php }; ?> >
                    <?php
                    echo date('d', strtotime($value['emp_ot_timeattandance']['att_date']));
                    ?>
                </td>
                <?php
            }
            ?>
        </tr>

        <tr>
            <td style='width:110px !important;'>Att In:</td>
            <?php
            foreach ($attendances as $value) {
                ?>
                <td><?php echo isset($value['emp_ot_timeattandance']['att_in_time'])?date('h:i:s', strtotime($value['emp_ot_timeattandance']['att_in_time'])):''; ?></td>

                <?php
            }
            ?>
        </tr>
        <tr>
            <td style='width:110px !important;'>Att Out:</td>
            <?php
            foreach ($attendances as $value) {
                ?>
                <td><?php echo isset($value['emp_ot_timeattandance']['att_out_time'])?date('h:i:s', strtotime($value['emp_ot_timeattandance']['att_out_time'])):''; ?></td>

                <?php
            }
            ?>
        </tr>
        <?php
        ?>
        <tr>
            <td>Min before on duty ot:</td>
            <?php foreach ($attendances as $val3) { ?><td><?php echo $val3['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot']; ?></td><?php } ?>
        </tr>
        <tr>
            <td>Min after off duty OT:</td>
<?php
foreach ($attendances as $val4) {
    ?>
                <td> <?php echo $val4['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot']; ?></td>

                <?php
            }
            ?>
        </tr>
        <tr>
            <td>OT Duration:</td>
            <?php
            foreach ($attendances as $val6) {
                $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                ?>
                <td> <?php echo $val6['emp_ot_timeattandance']['ot_duration']; ?></td>

                <?php
            }
            ?>
        </tr>
        <tr><td>OT Duration Hrs:</td>
            <?php
            foreach ($attendances as $val6) {
                ?>
                <td> <?php echo isset($val6['emp_ot_timeattandance']['ot_duration']) ? round(($val6['emp_ot_timeattandance']['ot_duration'] / 60), 2) : ''; ?></td>
                <?php
            }
            ?>
        </tr>
        <tr>
            <td>Total OT Duration</td>
            <td colspan="<?php echo $cnt; ?>">
            <?php
            echo $total . ' Min  -     ' . round(($total / 60), 2) . ' Hrs.';
            ?>
            </td>
        </tr>
    </table>


