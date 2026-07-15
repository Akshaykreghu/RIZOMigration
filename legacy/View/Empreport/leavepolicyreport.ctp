
<?php if ($mode == '') { ?>


    <section class="content">    <form role="form" id="form-shiftreport-master" method="POST" style="border-bottom: #C7BEBE 1px solid;">
            <div class="row"><div class="col-md-12">
                    <div class="box box-body">
                        <h1 class="page-header" style="text-align:center; "><strong>LEAVE POLICY REPORTS</strong> </h1>
                        <table class="table table-responsive table-hover" style="background-color:#fff ; " >
                            <thead>
                            <th>Leave Type</th>
                            <th>Allowed Leave For Year</th>
                            <th>Allowed Leave For Month</th>
                            <th>Carry Forward Limit</th>
                            <th>Leave Taken</th>
                            <th>Balance For The Year</th>
                            <!--<th>Applicable To</th>-->
<!--                            <th>Allow Negatives</th>
                            <th>Is Sandwich</th>
                            <th>Is Encash</th>-->
                            </thead>
                            <tbody>
                                <?php
                                foreach ($arr_leave as $val) {
                                    // debug($val);

                                    $balance_leave_year = isset($val['0']['leavebal']) ? $val['0']['leavebal'] : '';
                                    $allowed_leave_year = isset($val['leavepolicy']['alloted_leave_forthe_year']) ? $val['leavepolicy']['alloted_leave_forthe_year'] : '';
//check ALLOW negative   
                                    if ($val['leavepolicy']['ALLOW_NEGETIVE'] == 'Y') {
                                        $leave = "YES";
                                    } else {
                                        $leave = "NO";
                                    }
//sandwiche  
                                    if ($val['leavepolicy']['IS_SANDWICH'] == 'Y') {
                                        $sandleave = "YES";
                                    } else {
                                        $sandleave = "NO";
                                    }
                                    if ($val['leavepolicy']['is_leave_encash'] == 'Y') {
                                        $ench = "YES";
                                    } else {
                                        $ench = "NO";
                                    }
                                    $bal = (($allowed_leave_year) - ($balance_leave_year));


                                    if ($val['leavepolicy']['APPLICABLE_TO'] == 'A') {
                                        $applicable = "ALL";
                                    } else if ($val['leavepolicy']['APPLICABLE_TO'] == 'M') {
                                        $applicable = "Mens";
                                    } else {
                                        $applicable = "Womens";
                                    }
                                    ?>

                                    <?php echo'<tr>
               <td>' . $val['salary_head_items']['item'] . '</td>
                <td>' . $allowed_leave_year . '</td>
                <td>' . $val['leavepolicy']['alloted_leave_forthe_month'] . '</td>
                <td>' . $val['leavepolicy']['CARRY_FORWARD_LIMIT'] . '</td>
                <td>' . $bal . '</td>
                <td>' . $balance_leave_year . '</td>
                
     </tr>'; ?>


                                <?php } ?>
                            </tbody>


                        </table>
                        <br>
                        <div class="box">

                            <div class="col-md-12" align="right"><br>
                                <a href="<?php echo $this->webroot; ?>Empreport/leavepolicyreport/pdf" class="btn btn-default" ><i class="icon-file"></i>Download As PDF</a>
                                <!--<a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip','excel');"><i class="icon-file"></i>Download As Excel</a>-->
                            </div>
                        </div>
                    </div>
                    </form>
                </div></div>
    </section>

    <?php
} else {
    echo $this->element('reportempheader', array(
        "emp" => $arr_emp
    ));
    ?>

    <h3 style="text-align: center;padding-bottom: 10px;padding-top: 10px;">LEAVE POLICY REPORTS </h3>
    <table style="width: 100%;border: 1px;" >
        <tr style="font-weight: bold;border: 1px">
            <td style="border: 1px;width:10%">Leave Type</td>
            <td style="border: 1px;width:10%">Allowed Leave For Year</td>
            <td style="border: 1px;width:10%">Allowed Leave For Month</td>
            <td style="border: 1px;width:10%">Carry Forward Limit</td>
            <td style="border: 1px;width:10%">Leave Taken</td>
            <td style="border: 1px;width:10%">Balance For The Year</td>
            <!--<td style="border: 1px;width:10%">Applicable To</td>-->
<!--            <td style="border: 1px;width:10%">Allow Negatives</td>
            <td style="border: 1px;width:10%">Is Sandwich</td>
            <td style="border: 1px;width:10%">Is Encash</td>-->
        </tr>

        <?php
        foreach ($arr_leave as $val) {
            // debug($val);

            $balance_leave_year = isset($val['0']['leavebal']) ? $val['0']['leavebal'] : '';
            $allowed_leave_year = isset($val['leavepolicy']['alloted_leave_forthe_year']) ? $val['leavepolicy']['alloted_leave_forthe_year'] : '';
//check ALLOW negative   
            if ($val['leavepolicy']['ALLOW_NEGETIVE'] == 'Y') {
                $leave = "YES";
            } else {
                $leave = "NO";
            }
//sandwiche  
            if ($val['leavepolicy']['IS_SANDWICH'] == 'Y') {
                $sandleave = "YES";
            } else {
                $sandleave = "NO";
            }
            if ($val['leavepolicy']['is_leave_encash'] == 'Y') {
                $ench = "YES";
            } else {
                $ench = "NO";
            }
            $bal = (($allowed_leave_year) - ($balance_leave_year));


            if ($val['leavepolicy']['APPLICABLE_TO'] == 'A') {
                $applicable = "ALL";
            } else if ($val['leavepolicy']['APPLICABLE_TO'] == 'M') {
                $applicable = "Mens";
            } else {
                $applicable = "Womens";
            }
            ?>

            <?php echo'<tr style="border: 2px >
                  <td style="border: 1px">' . $val['salary_head_items']['item'] . '</td>
                    <td style="border: 1px">' . $allowed_leave_year . '</td>
                   <td style="border: 1px">' . $val['leavepolicy']['alloted_leave_forthe_month'] . '</td>
                    <td style="border: 1px">' . $val['leavepolicy']['CARRY_FORWARD_LIMIT'] . '</td>
                    <td style="border: 1px">' . $bal . '</td>
                    <td style="border: 1px">' . $balance_leave_year . '</td>
     </tr>'; ?>


        <?php } ?>



    </table>

<?php } ?>