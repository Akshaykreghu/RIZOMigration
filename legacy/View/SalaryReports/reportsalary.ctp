<style>
    .model-content {
        width: 118% !important;
    }

    .table,
    td,
    th,
    tr {
        border-style: solid;
        border-color: #d4d4de;

    }

    /* edited by athira on 18-02-2025 */
    #reportCon {
        padding: 10px 0 !important;
    }

    /* end */
</style>

<?php //debug($arr_leavepolicydetails_for_template);       
?>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:1%;  ">
        <?php $i = 0;
        foreach ($arr_leavepolicydetails_for_template as $value) {
            foreach ($value as $valuees) {
                //                             debug($valuees);
                if (!empty($valuees)) {
                    $i++;
                }
            }
        }
        if ($i <= '0') { ?>
            <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
                There is no data available</div>
        <?php } else { ?>
            <!-- edited by athira on 18-02-2025 -->
            <h3 align="center"><b>Cost To Company(CTC) Summary</b></h3>
            <h4 style="text-align:center;"><b>( Report Run by <?php echo $user_id . " at " . $date_time ?> )</b></h4>
            <!-- edited by athira on 15-07-2025 -->
            <?php if ($cr == 'Units') { ?>
                <h5 style="font-size:16px;"><b> <?php echo isset($value['summary'][0]['br']['branch_name']) ? $value['summary'][0]['br']['branch_name'] : ''  ?></b></h5>
            <?php } ?>
            <!-- end -->
            <div class="row">
                <div class="col-md-12" style="padding:0px;">
                    <!-- end -->


                    <div class="box-body">
                        <?php // debug($arr_leavepolicydetails_for_template);
                        ?>
                        <br>
                        <fieldset>


                            <table class="table ">
                                <thead>
                                    <tr>
                                        <!-- edited by athira on 18-02-2025 -->
                                        <th>Sl No</th>
                                        <th>Employee ID</th>
                                        <!-- edited by athira on 07-07-2025 -->
                                        <?php if ($company_code == 'DEMO' || $company_code == 'SRTS') { ?>
                                            <th>Employee ID (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                        <th>User ID </th>
                                        <th style="width:12%;">Employee Name</th>
                                        <!-- edited by athira on 07-07-2025 -->
                                        <?php if ($company_code == 'DEMO' || $company_code == 'SRTS') { ?>
                                            <th>Employee Name (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                        <th style="width:12%;"> Joining Date </th>
                                        <th>Branch</th>
                                        <th> Department </th>
                                        <th>Designation</th>
                                        <th style="width:14%;"> Termination Date </th>
                                        <!--modify by arun 15-10-2016  as per ashokan -->
                                        <!--<th style="width: 15%">Annual Gross Salary</th>-->
                                        <th>Monthly CTC</th>
                                        <th>Annual CTC</th>
                                        <!-- end -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $auualgross = 0;
                                    $monthlygross = 0;
                                    //   $sum = 0;
                                    $i = 0;

                                    foreach ($arr_leavepolicydetails_for_template as $value) {

                                        if (count($value['summary'])) {
                                    ?>
                                            <?php $arr_data = $value['summary']; ?>
                                            <?php
                                            if (count($arr_data) >= 0) {
                                                $si = 0;
                                            ?>
                                                <?php foreach ($arr_data as $val) { ?>

                                                    <tr> <?php 
                                                            $monthly = isset($val['0']['emp_derived_anualctc']) ? $val['0']['emp_derived_anualctc'] : 0;
                                                            $annual = isset($val['0']['emp_anual_ctc']) ? $val['0']['emp_anual_ctc'] : 0;
                                                            $auualgross = $auualgross + round($monthly * 12);
                                                            $monthlygross = $monthlygross + round($monthly);
                                                            $si = $si + 1;
                                                            ?>
                                                        <td><?php echo $i + 1; ?></td>
                                                        <td><?php echo $val['ep']['emp_company_id']; ?></td>
                                                        <!-- edited by athira on 07-07-2025 -->
                                                        <?php if ($company_code == 'DEMO' ||  $company_code == 'SRTS') { ?>
                                                            <td><?php echo $val['ed']['emp_us_company_id']; ?></td>
                                                        <?php } ?>
                                                        <!-- end -->
                                                        <!-- edited by athira on 18-02-2025 -->
                                                        <td><?php echo $val['uc']['user_id']; ?></td>
                                                        <td><?php echo $val['ed']['first_name'] . ' ' . $val['ed']['last_name'];
                                                            echo (isset($val['ed']['status'])) && $val['ed']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                        <!-- edited by athira on 07-07-2025 -->
                                                        <?php if ($company_code == 'DEMO' || $company_code == 'SRTS') { ?>
                                                            <td><?php echo $val['ed']['emp_us_name']; ?></td>
                                                        <?php } ?>
                                                        <!-- end -->
                                                        <!-- edited by athira on 28-02-2025 -->
                                                        <td><?php echo date('d-m-Y', strtotime($val['ep']['joining_date'])); ?></td>
                                                        <!-- end -->
                                                        <td><?php echo $val['br']['branch_name']; ?></td>
                                                        <td><?php echo $val['dep']['dept_name']; ?></td>
                                                        <td><?php echo $val['desig']['desig_name']; ?></td>
                                                        <!-- edited by athira on 01-03-2025 -->
                                                        <td><?php if (!empty($val['termination']['last_approved_working_date'])) {
                                                                echo date('d-m-Y', strtotime($val['termination']['last_approved_working_date']));
                                                            } else {
                                                                echo '';
                                                            }
                                                            ?>
                                                        </td>
                                                        <!-- end -->

                                                        <!--    modify by arun 15-10-2016  as per ashokan -->
                                                        <!--      <td><?php // echo $val['ectc']['emp_anual_ctc'];  
                                                                        ?></td>-->
                                                        <td><?php echo $monthly; ?></td>
                                                        <td><?php echo round($monthly * 12); ?></td>
                                                        <!--                                <td><?php echo round($annual); ?></td>-->
                                                    </tr>
                                                <?php
                                                    $i++;
                                                }
                                                ?>

                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="4">No employees found under this data</td>
                                                </tr>
                                            <?php } ?> <?php
                                                    }
                                                }
                                                        ?>
                                    <!-- edited by athira on 08-07-2025 -->
                                    <?php if ($company_code == 'DEMO' || $company_code == 'SRTS') { ?>
                                        <tr>
                                            <th colspan="11" style='text-align:center;'>TOTAL</th>
                                        <?php } else { ?>
                                        <tr>
                                            <th colspan="9" style='text-align:center;'>TOTAL</th>
                                        <?php } ?>

                                        <!-- end -->
                                        <!--                                        <th><?php //echo $auualgross; 
                                                                                        ?></th>-->
                                        <th><?php echo round($monthlygross); ?></th>
                                        <th><?php echo round($auualgross); ?></th>
                                        </tr>
                                </tbody>
                            </table>

                        </fieldset>
                        <br>
                    </div>
                    <!-- /.box-body -->

                </div>
            </div>

            <!--div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  

        <!--                    <a href="#" class="btn btn-default" onclick="downloadReport('salary', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>-->
            <!--                    <a href="#" class="btn btn-default" onclick="downloadReport('salary', 'excel');"><i class="icon-file"></i>Download As Excel</a>-->

            </div-->

    </div>
<?php }
    } else { ?>
<?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';    
?>
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

    td,
    th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>

<?php
        echo $this->element('reportadminheader', array(
            'title' => 'Cost To Company(CTC) Summary Report'
        ));
?>
<?php $i = 0;
        foreach ($arr_leavepolicydetails_for_template as $value) {
            foreach ($value as $valuees) {
                //                             debug($valuees);
                if (!empty($valuees)) {
                    $i++;
                }
            }
        }
        if ($i <= '0') { ?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available</div>
<?php } else { ?>

    <h4 style="text-align: left;padding-bottom: 10px;padding-top: 10px;"><?php
                                                                            if ($cr == 'Units') {
                                                                                $cr = 'Based on Branch';
                                                                            } else {
                                                                                $cr = 'Based on Employee';
                                                                            }
                                                                            echo $cr;
                                                                            ?></h4>

    <table class="table" align="center">
        <thead>
            <tr>
                <th style="width: 10%">Sl No</th>
                <th style="width: 20%">Employee ID</th>
                <th style="width: 15%">Employee Name</th>
                <th style="width: 10%">Branch</th>
                <th style="width: 15%">Designation</th>
                <!--                <th style="width: 15%">Annual Gross Salary</th>-->
                <th>Monthly CTC</th>
                <th style="width: 15%">Annual CTC</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $auualgross = 0;
            $monthlygross = 0;
            //   $sum = 0;
            $i = 0;

            foreach ($arr_leavepolicydetails_for_template as $value) {
                //debug($value);
                // debug($totel);  
                if (count($value['summary'])) {
            ?>
                    <?php $arr_data = $value['summary']; ?>
                    <?php
                    if (count($arr_data) >= 0) {
                        $si = 0;
                    ?>
                        <?php foreach ($arr_data as $val)
                        //  debug($val);


                        { ?>

                            <tr> <?php
                                    //  debug($val);
                                    //  $totel = $val['ectc']['emp_derived_anualctc'] * 12;
                                    $monthly = isset($val['0']['emp_derived_anualctc']) ? $val['0']['emp_derived_anualctc'] : 0;
                                    $annual = isset($val['0']['emp_anual_ctc']) ? $val['0']['emp_anual_ctc'] : 0;
                                    $auualgross = $auualgross + round($monthly * 12);
                                    $monthlygross = $monthlygross + round($monthly);

                                    //  $sum = $sum + $auualgross;
                                    // $annalsum = $annu + $val['ectc']['emp_anual_ctc'];
                                    // $sum = $sum + $val['ectc']['emp_anual_ctc'];
                                    //debug($annalsum);
                                    $si = $si + 1;
                                    ?>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo $val['a']['emp_company_id']; ?></td>
                                <td><?php echo $val['a']['first_name'] . ' ' . $val['a']['last_name'];  ?></td>
                                <td><?php echo $val['a']['branch_name']; ?></td>
                                <td><?php echo $val['a']['desig_name']; ?></td>

                                <!--    modify by arun 15-10-2016  as per ashokan -->
                                <!--      <td><?php // echo $val['ectc']['emp_anual_ctc'];  
                                                ?></td>-->
                                <td><?php echo $monthly; ?></td>
                                <td><?php echo round($monthly * 12); ?></td>
                                <!--                                <td><?php echo round($annual); ?></td>-->
                            </tr>
                        <?php
                            $i++;
                        }
                        ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="4">No employees found under this data</td>
                        </tr>
                    <?php } ?> <?php
                            }
                        }
                                ?>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <th>Total</th>
                <!--                                        <th><?php //echo $auualgross; 
                                                                ?></th>-->
                <th><?php echo round($monthlygross); ?></th>
                <th><?php echo round($auualgross); ?></th>
            </tr>
        </tbody>
    </table>


    <br>
    <!-- /.box-body -->

<?php  //die();

        }
    } ?>