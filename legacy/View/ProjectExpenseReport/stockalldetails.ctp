<style>

    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;


    }

</style>

<?php //debug($arr_stocksummary_for_template);?>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" >
        <h3 align="center" style="font-weight:bold; font-size: 30px;"><fieldset> 
                ITEM DETAILS REPORT <?php //echo $month;     ?> 
            </fieldset></h3>
        <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
        <div class="row">

            <!--            <fieldset> 
                         <legend><?php //echo isset($user_id) ? ($user_id)." - ".$date_time : '';     ?></legend>
                     </fieldset>-->
            <?php
            $i = 0;
            if (count($arr_stocksummary_for_template) == 0) {
                echo "<h2>No Data Available With The Selected Criteria</h2> ";
            } else {
                foreach ($arr_stocksummary_for_template as $value) {
                    ?>
                    <!--     debug($value);-->
                    <div class="col-md-12"  >

                        <?php
                        if (count($value) !== 0) {
                            $i += 1;
                            ?>

                            <fieldset> 
                                <legend> <?php
                                    echo isset($value['0']['stock_details_view']['store_location']) ? "Store - " . $value['0']['stock_details_view']['store_location'] : '';
                                    echo ' ';
                                    ?> 
                                </legend>
                            </fieldset>

                            <br>
                            <div class="row">
                                <div class="col-md-12" style="overflow-y:auto;">


                                                    <!--                            <div class="col-md-4">ITEM CODE : <?php //echo isset($value['summary']['0']['ep']['emp_company_id']) ? $value['summary']['0']['ep']['emp_company_id'] : '';     ?>  </div>
                                                    <div class="col-md-4">ITEM NAME : <?php //echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';     ?>  </div>              
                                                    <div class="col-md-4">QTY : <?php //echo isset($value['summary']['0']['desg']['desig_name']) ? $value['summary']['0']['desg']['desig_name'] : '';     ?>  </div>               
                                                    <div class="col-md-4">STORE CODE : <?php //echo isset($value['summary']['0']['dpt']['dept_name']) ? $value['summary']['0']['dpt']['dept_name'] : '';     ?>  </div>-->



                                    <fieldset>


                                        <table class="table">
                                            <thead>
                                                <tr>

                                                    <th>Sl No</th>
                                                    <th>Item Code </th>
                                                    <th>Item Name</th>
                                                    <th>Store</th>
                                                    <th>Invoice Number</th>
                                                    <th>MR Number</th>
                                                    <th>PO Number</th>
                  <!--                              <th>Item Batch</th>
                                                    <th>Received Quantity</th>-->
                                                    <th>Item Quantity</th>
                  <!--                              <th>Free Stock</th>
                                                    <th>Offer Stock</th>-->
                  <!--                              <th>Purchase Rate</th>
                                                    <th>Amount</th>
                                                    <th>Item Rate</th>-->
                  <!--                              <th>Varified Person</th>-->
                                                    <th>Transaction Type</th>
                                                    <th>Created Person</th>
                                                    <th>Creation Date</th>
                                                    <th>Modified Person</th>
                                                    <th>Modification Date</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $arr_data = $value;
                                                //debug($arr_data) ; 
                                                ?>
                                                <?php
                                                if (count($arr_data) >= 0) {
                                                    $i = 0;
                                                    $sum = 0;
                                                    ?>
                                                    <?php foreach ($arr_data as $val) { ?>
                                                        <tr>
                                                            <?php $i = $i + 1; //$sum +=  $val['0']['balance_qty'];    ?>
                                                            <td><?php echo $i; ?></td>
                                                            <td><?php echo $val['stock_details_view']['item_code']; ?></td>
                                                            <td><?php echo $val['stock_details_view']['item_desc']; ?></td>
                                                            <td><?php echo $val['stock_details_view']['store_location']; ?></td>
                                                            <td><?php echo $val['stock_details_view']['invoice_no']; ?></td>
                                                            <td><?php echo $val['stock_details_view']['mr_no']; ?></td>
                                                            <td><?php echo $val['stock_details_view']['po_no']; ?></td>
                        <!--                                <td><?php //echo $val['stock_details_view']['item_batch'];     ?></td>
                                                            <td><?php //echo $val['stock_details_view']['received_qty'];     ?></td>-->
                                                            <td><?php echo $val['stock_details_view']['item_qty']; ?></td>
                        <!--                                <td><?php // echo $val['sd']['free_stock'];     ?></td>
                                                            <td><?php //echo $val['sd']['offer_stock'];     ?></td>-->
                        <!--                                <td><?php //echo $val['stock_details_view']['purchase_rate'];     ?></td>
                                                            <td><?php //echo $val['stock_details_view']['amount'];     ?></td>
                                                            <td><?php //echo $val['stock_details_view']['item_mrp'];     ?></td>-->
                        <!--                                 <td><?php //echo $val['stock_details_view']['varified_by'];     ?></td>-->
                                                            <td><?php echo $val['stock_details_view']['item_state']; ?></td>
                                                            <td><?php echo $val['stock_details_view']['created_by']; ?></td>
                                                            <td><?php echo date("Y-m-d", strtotime($val['stock_details_view']['creation_date']));  ?></td>
                                                            <td><?php echo $val['stock_details_view']['modified_by']; ?></td>
                                                            <td><?php  if($val['stock_details_view']['modified_date']){echo date("Y-m-d", strtotime($val['stock_details_view']['modified_date']));} ?></td>
                                                        </tr>





                                                    <?php } ?>

                                                <?php } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="4">No Stocks found under this data</td>
                                                    </tr>  
                                                <?php } ?>


                                            </tbody>
                                        </table>

                                    </fieldset>
                                </div>
                            </div>
                            <br>


                            <?php
                        }
                        //else { echo "No Data Available With The Selected Criteria "; } 
                        ?>
                    </div>
                <?php
                }
            }
            ?> <!-- /.box-body -->


        </div>  

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
        table td,th.set{width:50px;}
        td, th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 1px solid #B2B2B2;
        }
        th.narrow{width:50px;}

    </style>

    <?php
echo $this->element('reportadminheader',array(
'title'=>'Item Details Report'));
    ?>
    <h3>(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)
    </h3>
    <?php
    $i = 0;
    if (count($arr_stocksummary_for_template) == 0) {
        echo "<h2>No Data Available With The Selected Criteria</h2> ";
    } else {
        foreach ($arr_stocksummary_for_template as $value) {
            if (count($value) !== 0) {
                $i += 1;
                ?>

																								
							 
                <h3 style="text-align: left;padding-bottom: 0px;padding-top: 10px;"><?php
                    echo isset($value['0']['stock_details_view']['store_location']) ? "Store - " . $value['0']['stock_details_view']['store_location'] : '';
                    echo ' ';
                    ?> 
                </h3>


                <hr>

                <br>



                <table class="table" align="center">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Item Code </th>
                            <th class="narrow" scope="col">Item Name</th>
                            <th >Store</th>
                            <th>Invoice <br/> Number</th>
                            <th>MR Number</th>
                            <th>PO Number</th>
                <!--                              <th>Item <br/> Batch</th>
                            <th>Received <br/> Quantity</th>-->
                            <th>Item <br/> Quantity</th>
                <!--                              <th>Free <br/> Stock</th>
                            <th>Offer <br/> Stock</th>-->
                <!--                              <th>Purchase <br/> Rate</th>
                            <th>Amount</th>
                            <th>Item <br/> Rate</th>-->
                <!--                              <th>Varified <br/> Person</th>-->
                            <th>Transaction <br/> Type</th>
                            <th>Created <br/> Person</th>
                            <th class="set" scope="col">Creation <br/> Date</th>
                            <th>Modified <br/> Person</th>
                            <th>Modification <br/> Date</th>

                        </tr>
                    </thead>

                    <tbody>

                        <?php $arr_data = $value;
                        ?>
                        <?php
                        if (count($arr_data) >= 0) {
                            $i = 0;
                            $sum = 0;
                            ?>
                                <?php foreach ($arr_data as $val) { ?>
                                <tr>
                        <?php $i = $i + 1; //$sum +=  $val['0']['balance_qty'];     ?>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $val['stock_details_view']['item_code']; ?></td>
                                    <td><?php echo $val['stock_details_view']['item_desc']; ?></td>
                                    <td><?php echo $val['stock_details_view']['store_location']; ?></td>
                                    <td><?php echo $val['stock_details_view']['invoice_no']; ?></td>
                                    <td><?php echo $val['stock_details_view']['mr_no']; ?></td>
                                    <td><?php echo $val['stock_details_view']['po_no']; ?></td>
                        <!--                                <td><?php //echo $val['stock_details_view']['item_batch'];     ?></td>
                                    <td><?php //echo $val['stock_details_view']['received_qty'];     ?></td>-->
                                    <td><?php echo $val['stock_details_view']['item_qty']; ?></td>
                        <!--                                <td><?php //echo $val['sd']['free_stock'];     ?></td>
                                    <td><?php //echo $val['sd']['offer_stock'];     ?></td>-->
                        <!--                                <td><?php //echo $val['stock_details_view']['purchase_rate'];     ?></td>
                                    <td><?php //echo $val['stock_details_view']['amount'];     ?></td>
                                    <td><?php //echo $val['stock_details_view']['item_mrp'];     ?></td>-->
                        <!--                                <td><?php //echo $val['stock_details_view']['varified_by'];    ?></td>-->
                                    <td><?php echo $val['stock_details_view']['item_state']; ?></td>
                                    <td><?php echo $val['stock_details_view']['created_by']; ?></td>
                                    <td class="set" scope="col"><?php echo date("Y-m-d", strtotime($val['stock_details_view']['creation_date'])); ?></td>
                                    <td><?php echo $val['stock_details_view']['modified_by']; ?></td>
                                    <td><?php if($val['stock_details_view']['modified_date']){echo date("Y-m-d", strtotime($val['stock_details_view']['modified_date']));} ?></td>
                                </tr>     




                            <?php } ?>

                <?php } else { ?>
                            <tr>
                                <td colspan="4">No Stocks found under this data</td>
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

