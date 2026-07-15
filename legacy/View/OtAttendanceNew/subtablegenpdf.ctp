<style type="text/css">
    body {
        line-height: 1.8em;
        font-size: 11px;
    }

    table {
        border-collapse: collapse;
        border-spacing: 0;
    }

    td,
    th {
        text-align: center;
        padding: 7px 10px;
        border: 1px solid #B2B2B2;
        white-space: nowrap;
        font-size: 11px;

        min-width: 75px;
    }

    th:first-child,
    td:first-child {
        width: 180px;
        text-align: left;
        font-weight: bold;
        padding-left: 10px;
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

<div style="width:100%;">

    <!-- Heading -->
    <div style="width:50%; text-align:center; margin-bottom:15px;">

        <div style="font-size:20px; font-weight:bold;">
            OVERTIME REPORT
        </div>

        <div style="font-size:14px; margin-top:5px;">
            <?php echo date('M - Y', strtotime($month)); ?>
        </div>

    </div>

    <!-- Table -->
    <div style="width:100%; text-align:center;">

        <table class="table table-bordered table-responsive" cellspacing="0" border="0">
            <tr>
                <th></th>
                <?php
                foreach ($attendances as $value) {
                ?>
                    <?php
                    $style = '';

                    if ($value['edt']['present'] == '') {
                        $style .= 'background:#0AC5B6;font-weight:bold;';
                    }

                    if ($value[0]['off_type'] == '/WO') {
                        $style .= 'background:#C4A484 ;color:white ;font-weight:bold;';
                    }

                    if ($value[0]['off_type'] == 'WO') {
                        $style .= 'background:yellow;color:black;font-weight:bold;';
                    }

                    if ($value[0]['off_type'] == 'HO') {
                        $style .= 'background:blue;color:black;font-weight:bold;';
                    }

                    if ($value['edt']['present'] == 'A/A') {
                        $style .= 'color:red;font-weight:bold;';
                    }

                    if ($value['edt']['present'] == 'P/P') {
                        $style .= 'color:green;font-weight:bold;';
                    }
                    ?>
                    <th style="<?php echo $style; ?>">
                        <?php
                        echo !empty($value['dates']['att_date'])
                            ? date('d', strtotime($value['dates']['att_date']))
                            : '';
                        ?>
                    </th>
                <?php
                }
                ?>
            </tr>

            <tr>
                <td style='width:110px !important;'>Att In:</td>
                <?php
                foreach ($attendances as $value) {
                ?>
                    <td><?php echo !empty($value[0]['att_in_time']) ? date('h:i:s', strtotime($value[0]['att_in_time'])) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?></td>

                <?php
                }
                ?>
            </tr>
            <tr>
                <td style='width:110px !important;'>Att Out:</td>
                <?php
                foreach ($attendances as $value) {
                ?>
                    <td><?php echo (isset($value[0]['att_out_time']) && $value[0]['att_out_time'] != '') ? date('h:i:s', strtotime($value[0]['att_out_time'])) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?></td>

                <?php
                }
                ?>
            </tr>
            <?php
            ?>
            <tr>
                <td>Min before on duty ot:</td>
                <?php foreach ($attendances as $val3) { ?><td><?php echo $val3['eot']['min_bfr_on_dutty_cal_ot']; ?></td><?php } ?>
            </tr>
            <tr>
                <td>Min after off duty OT:</td>
                <?php
                foreach ($attendances as $val4) {
                ?>
                    <td> <?php echo $val4['eot']['min_aftr_off_dutty_cal_ot']; ?></td>

                <?php
                }
                ?>
            </tr>
            <tr>
                <td>OT Duration:</td>
                <?php
                foreach ($attendances as $val6) {
                    // $total = $total + $val6['eot']['ot_duration']; // Edited by Akshay on 19-12-2025
                ?>
                    <td> <?php echo $val6[0]['ot_duration']; ?></td>

                <?php
                }
                ?>
            </tr>
            <tr>
                <td>OT Duration Hrs:</td>
                <?php
                foreach ($attendances as $val6) {
                ?>
                    <td> <?php echo (isset($val6[0]['ot_duration']) && $val6[0]['ot_duration'] != '' )? round(($val6[0]['ot_duration'] / 60), 2) : ''; ?></td>
                <?php
                }
                ?>
            </tr>
            <!-- Edited by Akshay on 19-12-2025 -->
            <tr>
                <td>Set Duration:</td>
                <?php
                foreach ($attendances as $val6) {
                    $total += !empty($val6[0]['set_duration'])
                        ? $val6[0]['set_duration']
                        : 0;
                ?>
                    <td> <?php echo isset($val6[0]['set_duration']) ? $val6[0]['set_duration'] : ''; ?></td>
                <?php
                }
                ?>
            </tr>
            <tr>
                <td>Set Duration Hrs:</td>
                <?php
                foreach ($attendances as $val6) {
                ?>
                    <td> <?php echo (isset($val6[0]['set_duration']) && $val6[0]['set_duration'] != '')? round(($val6[0]['set_duration'] / 60), 2) : ''; ?></td>
                <?php
                }
                ?>
            </tr>
            <!-- End -->
            <tr>
                <td>Total OT Duration</td>
                <td colspan="<?php echo $cnt; ?>">
                    <?php
                    echo $total . ' Min  -     ' . round(($total / 60), 2) . ' Hrs.';
                    ?>
                </td>
            </tr>

            <!-- Edited by Akshay on 22-5-2026 -->
            <?php
            if ($set_ot_duration != '') { ?>
                <tr>
                    <td>New OT Duration</td>
                    <td colspan="<?php echo $cnt; ?>">
                        <?php
                        echo $new_ot_duration . ' Min  -     ' . round(($new_ot_duration / 60), 2) . ' Hrs.';
                        ?>
                    </td>
                </tr>
            <?php }
            ?>
            <!-- End -->
        </table>
    </div>

</div> 