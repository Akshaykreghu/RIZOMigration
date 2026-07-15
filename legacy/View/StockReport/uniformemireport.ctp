<!-- <script type="text/javascript">
        function viewComplete() {
            $('#loaders').show();
            var type = $('#hidden_type').val();

            // console.log(criteria);

            //var container = $("#largeModalForm #largeModalForm-content");  

            var url = livesite + 'StockReport/GenerateCompletedLoan/' + type;

            $('body').addClass('sidebar-collapse');
            container.load(url, $('#form-showreport').serialize(), function () {
                $('#loaders').hide();
                //$("#largeModalForm").modal('show')
            });


        }
    </script>-->
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" >
        <h3 align="center" style="font-weight:bold; font-size: 30px;">Uniform Allocation EMI  Report : <?php echo $from; ?> - <?php echo $otdate; ?></h3>
        <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
        <div class="row">
            <div class="col-md-12">
<!--                <label style="margin-left: 1200px;">Completed Loans</label>
                <button type="button" style="margin-left: 1350px; " id="btn-submit2" class="btn btn-success" onclick="viewComplete();"><li class="fa fa-eye"></li></button>-->

                <?php
                $i = 0;
                if (count($arr_stocksummary_for_template) == 0) {
                    echo "<h2>No Data Available With The Selected Criteria</h2> ";
                } else {
                    foreach ($arr_stocksummary_for_template as $value) {
                        if (count($value) !== 0) {
                            $i += 1;
                            $j = $i - 1;
                            ?>

                            <fieldset> 


                                <legend> <?php
                                    if ($type == 'EmployeeDetails') {
                                        echo isset($value['0']['employee_info']['EmpName']) ? "Uniform Allocation EMI Report of - " . $value['0']['employee_info']['EmpName'] : '';
                                    } else {
                                        echo isset($value['0']['store_master']['store_location']) ? "Uniform Allocation EMI  Report of - " . $value['0']['store_master']['store_location'] : '';
                                    }
                                    echo ' ';
                                    ?> 
                                </legend>
                                <div class="col-md-4" > 
                                    <input type="hidden" name="hidden_type" id="hidden_type" value="isset($value['0']['employee_info']['emp_pkey']) ?  $value['0']['employee_info']['emp_pkey'] : '';">
                                </div>

                                <div class="row">
                                    <div class="col-md-12">

                                    </div>
                                </div>
                            </fieldset>

                            <br>
                            <fieldset>


                                <table class="table table-bordered " >
                                    <thead>
                                        <tr>

                                            <th>Sl No</th>
                                            <th>Employee Name </th>
                                            <th>Employee ID </th>
                                            <th>Date Of Joining </th>
                <!--                                        <th>Branch </th>
                                            <th>Department </th>-->
                                            <th>Designation </th>                                        
                                            <th>Store Name</th>
                                            <th>Issued Date</th>
                                            <!--<th>Issued Qty</th>-->
                                            <th>Total Value</th>   
                                            <?php for ($i = 1; $i <= $maxemicount; $i++) { ?>
                                                <th><?php echo $i; ?> EMI</th>
                                            <?php } ?>
                                            <th>Balance</th>

                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $arr_data = $value; ?>
                                        <?php
                                        if (count($arr_data) >= 0) {
                                            $s = 0;
                                            $sum = 0;
                                            ?>
                                            <?php foreach ($arr_data as $val) { ?>
                                                <tr>
                                                    <?php
                                                    $s = $s + 1;
                                                    //$sum += $val['0']['balance_qty']; 
                                                    ?>
                                                    <td><?php
                                                        echo $s;
//                                                    $PO_Status = $val['po']['grn_status'];
//                                                    $string_po = '';
//                                                    switch ($PO_Status){
//                                                        case "0": $string_po = "GRN Received";
//                                                            break;
//                                                        case "1": $string_po = "PO Ordered";
//                                                            break;
//                                                        case "2": $string_po = "Finalised";
//                                                            break;
//                                                        default : $string_po = "PO";
//                                                            break;
//                                                    }
                                                        ?></td>

                                                    <td><?php echo $val['employee_info']['EmpName']; ?></td>
                                                    <td><?php echo $val['employee_info']['employee_id']; ?></td>
                                                    <td><?php echo $val['employee_info']['joining_date']; ?></td>
                        <!--                        <td><?php echo $val['employee_info']['branch']; ?></td>
                                                    <td><?php echo $val['employee_info']['department']; ?></td>-->
                                                    <td><?php echo $val['employee_info']['designation']; ?></td>
                                                    <td><?php echo $val['store_master']['store_location']; ?></td>
                                                    <td><?php echo $val['itm_allocation']['date_allocated']; ?></td>
                                                    <!--<td><?php echo $val['allocate_details']['qty']; ?></td>-->
                                                    <td><?php echo $val['itm_allocation']['value']; ?></td>
                                                    <?php
                                                    $paid = 0;
                                                    for ($i = 0; $i < $maxemicount; $i++) {
//     debug($val['emi']);
                                                        ?>
                                                        <td><?php
                                                            $paid+=isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0';
                                                            echo isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0';
                                                            ?></td>
                                                    <?php } ?>

                                                    <td><?php echo $val['itm_allocation']['value'] - $paid; ?></td>
                                                </tr>





                                            <?php } ?>

                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="4">No Orders found under this data</td>
                                            </tr>  
                                        <?php } ?>


                                    </tbody>
                                </table>

                            </fieldset>

                            <br>


                            <?php
                        }
                    }
                }
                ?> <!-- /.box-body -->

            </div>
        </div>  
        <h3>** Please choose Uniform allocated date for viewing report **</h3>
        <!--div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  
        </div-->
        <!---<div class="row">
              <div class="form-group">
                  <div class="col-md-12" align="right">
                      <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                      <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','excel');"><i class="icon-file"></i>Download As Excel</a>
                  </div>
              </div>
          </div> -->
    </div>

<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';     ?>
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
            border: 2px solid #f4f4f4;
            width: 100%;
            max-width: 100%;
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
            font-size: 10px;
            border: 1px solid #B2B2B2;
        }
    </style>
  

    <?php
    echo $this->element('reportadminheader', array(
     'title' => 'Uniform Allocation EMI Report - '.$from.' - '.$otdate.''));
    ?>
   <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
        
    <?php
    $i = 0;
    if (count($arr_stocksummary_for_template) == 0) {
        echo "<h2>No Data Available With The Selected Criteria</h2> ";
    } else {
        foreach ($arr_stocksummary_for_template as $value) {
            if (count($value) !== 0) {
                $i += 1;
                ?>
				 
														   
																					   
			  


                <h3 style="text-align: left;padding-bottom: 0px;padding-top: 10px;"> <?php
                    if ($type == 'EmployeeDetails') {
                        echo isset($value['0']['employee_info']['EmpName']) ? "Uniform Allocation EMI Report of - " . $value['0']['employee_info']['EmpName'] : '';
                    } else {
                        echo isset($value['0']['store_master']['store_location']) ? "Uniform Allocation EMI Report of - " . $value['0']['store_master']['store_location'] : '';
                    }
                    ?>
                </h3>


                <hr>

                <br>



                <table class="table">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee Name </th>
                            <th>Employee ID </th>
                            <th>Date Of Joining </th>
                <!--        <th>Branch </th>
                            <th>Department </th>-->
                            <th>Designation </th>                                        
                            <th>Store Name</th>
                            <th>Issued Date</th>
                <!--        <th>Issued Qty</th>-->
                            <th>Total Value</th>   
                            <?php for ($i = 1; $i <= $maxemicount; $i++) { ?>
                                <th><?php echo $i; ?> EMI</th>
                            <?php } ?>
                            <th>Balance</th>

                        </tr>
                    </thead>

                    <tbody>
                        <?php $arr_data = $value; ?>
                        <?php
                        if (count($arr_data) >= 0) {
                            $s = 0;
                            $sum = 0;
                            ?>
                            <?php foreach ($arr_data as $val) { ?>
                                <tr>
                                    <?php
                                    $s = $s + 1;
                                    //$sum += $val['0']['balance_qty']; 
                                    ?>
                                    <td><?php
                                        echo $s;
//                                                    $PO_Status = $val['po']['grn_status'];
//                                                    $string_po = '';
//                                                    switch ($PO_Status){
//                                                        case "0": $string_po = "GRN Received";
//                                                            break;
//                                                        case "1": $string_po = "PO Ordered";
//                                                            break;
//                                                        case "2": $string_po = "Finalised";
//                                                            break;
//                                                        default : $string_po = "PO";
//                                                            break;
//                                                    }
                                        ?></td>
                                    <td><?php echo $val['employee_info']['EmpName']; ?></td>
                                    <td><?php echo $val['employee_info']['employee_id']; ?></td>
                                    <td><?php echo $val['employee_info']['joining_date']; ?></td>
                        <!--                                                <td><?php echo $val['employee_info']['branch']; ?></td>
                                    <td><?php // echo $val['employee_info']['department'];      ?></td>-->
                                    <td><?php echo $val['employee_info']['designation']; ?></td>
                                    <td><?php echo $val['store_master']['store_location']; ?></td>
                                    <td><?php echo $val['itm_allocation']['date_allocated']; ?></td>
                                    <!--<td><?php echo $val['allocate_details']['qty']; ?></td>-->
                                    <td><?php echo $val['itm_allocation']['value']; ?></td>
                                    <?php
                                    $paid = 0;
                                    for ($i = 0; $i < $maxemicount; $i++) {
//     debug($val['emi']);
                                        ?>
                                        <td><?php
                                            $paid+=isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0';
                                            echo isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0';
                                            ?></td>
                                    <?php } ?>

                                    <td><?php echo $val['itm_allocation']['value'] - $paid; ?></td>
                                </tr>





                            <?php } ?>

                        <?php } else { ?>
                            <tr>
                                <td colspan="4">No Orders found under this data</td>
                            </tr>  
                        <?php } ?>


                    </tbody>
                </table>


                <br>


                <?php
            }
        }
    }
    ?> <!-- /.box-body -->

<?php } ?>  