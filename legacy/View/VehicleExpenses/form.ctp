<script>
    $.validate({
        form: '#vehicle_expance'
    });
    var options = {
        success: function (resp) {
            $('#modalForm').modal('hide');
            $('#att_table').datagrid('reload');
//            $('#myleaverequeststable').datagrid('reload');
            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
$('#expensedate').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });

    $('#vehicle_expance').on('submit', function (event) {
        event.preventDefault();
        if ($('#starting_date').val() == "" || $('#ending_date').val() == "") {
            nofityMsg('Please choose date');
            return false;
        }
        if ($("#starting_km").val() <= 0 || $("#ending_km").val() <= 0) {
            nofityMsg('Distance should be greater than 0');
            return false;
        }
        $("#btn-submit").prop("value", "Saving");
        $("#btn-submit").prop("disabled", true);
        setTimeout(
                function () {
                    if (confirm(" Do You Want  To Save The Form")) {
                        $('#vehicle_expance').ajaxSubmit(options)
                    } else {
                        $("#btn-submit").prop("value", "Save");
                        $("#btn-submit").prop("disabled", false);
                    }
                }, 1000
                );
    });


</script>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> Vehicle Expenses</h4>  
        </div>
        <?php $pkey = isset($data["transportation_expense_pkey"])?$data["transportation_expense_pkey"]:''; ?>
        <div class="modal-body">
            <!-- Form starts -->
            <form class="form-horizontal" id="vehicle_expance" action="<?php echo $this->webroot; ?>VehicleExpenses/employeeloansave" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="driver_name" class="col-sm-4 control-label">Driver Name<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo $data['driver_name'] ?>" name="driver_name" id="driver_name" style="background:white;" placeholder="Enter Driver Name"  required="required">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="vehicle_master_fkey" class="col-sm-4 control-label">Vehicle Register No.<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="vehicle_master_fkey" class="form-control" name="vehicle_master_fkey" required="required" style="width: 285px;">
                                    <option value="">[--Select--]</option>
                                    <?php
                                    foreach ($arr_vehicles as $value) {
                                        $selected = ($data['vehicle_master_fkey'] == $value['vehicle_master']['vehicle_master_pkey']) ? 'selected="selected"' : '';
                                        echo '<option value="' . $value['vehicle_master']['vehicle_master_pkey'] . '" ' . $selected . '>' . $value['vehicle_master']['reg_number'] . ' - ' . $value['vehicle_master']['model_dec'] . '</option>';
                                    }
                                    ?>
                                </select> 
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="site_fkey" class="col-sm-4 control-label">Project Name</label>
                            <div class="col-md-7">
                                <select id="site_fkey" name="site_fkey" class="form-control" style="width: 285px" required="required">
                                    <option value="">[--Select--]</option>
                                    <?php
                                    foreach ($arr_site as $list) {
                                        foreach ($list as $l) {
                                            $selected = ($data['site_fkey'] == $l['site_pkey']) ? 'selected="selected"' : '';
                                            ?>
                                            <option value="<?php echo $l["site_pkey"] ?>" <?php echo $selected; ?> ><?php echo $l["site_id"] . ' - ' . $l["site_name"] ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="purpose" class="col-sm-4 control-label">Purpose<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo $data['purpose']; ?>" name="purpose" id="purpose" placeholder="Enter a Purpose" required="required">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="starting_date" class="col-sm-4 control-label">Starting Date<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo $data['starting_date']; ?>" name="starting_date" id="starting_date" readonly="readonly" style="background:white;" placeholder="Select Date" required="required">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="starting_km" class="col-sm-4 control-label">Starting km<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="number"  class="form-control" value="<?php echo $data['starting_km']; ?>" name="starting_km" id="starting_km" style="background:white;" placeholder="Enter Starting km" required="required" autocomplete="off">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="ending_date" class="col-sm-4 control-label">Ending Date<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo $data['ending_date']; ?>" name="ending_date" id="ending_date" readonly="readonly" style="background:white;" placeholder="Select Date" required="required">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="ending_km" class="col-sm-4 control-label">Ending km<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="number"  class="form-control" value="<?php echo $data['ending_km']; ?>" name="ending_km" id="ending_km" style="background:white;" placeholder="Enter Ending km" required="required" autocomplete="off">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="total_km" class="col-sm-4 control-label">Total km<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo $data['total_km']; ?>" name="total_km" id="total_km" readonly="readonly" placeholder="Enter Total km" required="required">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="total_cost" class="col-sm-4 control-label">Expense Amount<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="number" required="required" class="form-control" value="<?php echo $data['total_cost'] ?>" name="total_cost" id="total_cost" onblur="findamount();" readonly="readonly">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                      <div class="col-md-12">
                      <?php if($pkey > 0) { ?>
                          <label class="col-sm-4 control-label" for="payments" style="text-align:left;">Total Amount Received</label>  
                      <?php }else{ ?>
                          <label class="col-sm-4 control-label" for="payments" style="text-align:left;">Amount Received</label>  
                      <?php } ?>
                          <div class="col-md-7">
                              <input id="payments" name="payments" <?php if($pkey > 0) { ?>readonly="readonly"<?php } ?> onchange=" add();" min="0" value="<?php echo isset($data["payments"])?$data["payments"]:'0'; ?>" type="number" step="any" placeholder="Amount Received" class="form-control input-md">
                          </div>
                      </div>
                    </div>
                    <?php if($pkey > 0) { ?>
                    <div class="form-group">
                      <div class="col-md-12">
                        <label class="col-md-4 control-label" for="payamount" style="text-align:left;">Amount Received</label>  
                        <div class="col-md-7">
                            <input id="payamount" name="payamount" onchange="minus();" value="" min="0" max="<?php echo isset($data["balance"])?$data["balance"]:''; ?>" type="number" step="any" placeholder="Amount Received" class="form-control input-md">
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-md-12">
                         <label class="col-md-4 control-label" for="expensedate" style="text-align:left;">Payment Received Date</label>  
                         <div class="col-md-7">
                           <input id="expensedate" name="expensedate"  value="" type="text" placeholder="Payment Received Date" class="form-control input-md" >
                         </div>
                      </div>
                    </div>
                    <?php } ?>
                    <div class="form-group">
                      <div class="col-md-12">
                         <label class="col-md-4 control-label" for="balance" style="text-align:left;"> Balance</label>  
                         <div class="col-md-7">
                            <input id="balance" name="balance" readonly="readonly" value="<?php echo isset($data["balance"])?$data["balance"]:''; ?>" type="number" step="any" placeholder="Balance" class="form-control input-md" required="required">
                         </div>
                      </div>
                    </div>
<!--                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="image" class="col-sm-4 control-label">Image</label>
                            <div class="col-md-7">
                                <input type="file" class="form-control file" id="image" name="image" >
                            </div> 
                        </div>
                    </div>-->
                    <!--                    <div class="form-group">
                                            <div class="col-md-10">
                                                <label for="in_date" class="col-sm-4 control-label">Remark</label>
                                                <div class="col-md-7">
                                                    <input type="text"  class="form-control" value="<?php echo $data['remarks'] ?>" name="remarks" id="remarks" placeholder="Enter Remarks">
                                                </div> 
                                            </div>
                                        </div>-->
                    <div class="modal-footer">
                        <input type="hidden" required="required" class="form-control" value="<?php echo $data['transportation_expense_pkey'] ?>" name="transportation_expense_pkey">
                        <input type="hidden" required="required" class="form-control" value="<?php echo $data['rate_per_km']; ?>" name="rate_per_km" id="rate_per_km">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="btn-submit" id="btn-submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
            <!-- Tax Head Detail Form -->
            <!-- form ends-->
        </div>
    </div>
</div>
<script type="text/javascript">
     function add(){
         var total = parseFloat($('#total_cost').val());
         var payments = parseFloat($('#payments').val());
       if(payments > 0){
         if(payments <= total){
         var balance = total - payments ;
         }else{
         alert("Amount received should be less than balance.");
         $("#payments").val('');
         var balance = total; 
         }
         }else{
         var balance = total ;    
         }
         $("#balance").val(balance);
    }
    function minus(){
        var payments = parseFloat($('#payments').val());
        var payamount = parseFloat($('#payamount').val());
        var total = parseFloat($('#total_cost').val());
        var balance = parseFloat($('#balance').val());
        var transportation_expense_pkey = parseFloat($('#transportation_expense_pkey').val()); 
        if(payments > 0){
       if(payments <= total){
        if(payamount){
            if(payamount <= balance){
        var balance = total - payments - payamount;
        }
    else{
        alert("Amount received should be less than balance");
        $("#payamount").val('');
        var balance = total - payments; 
    }
        }else{
        var balance = total - payments;    
        }
        $("#balance").val(balance);
    }
    else{
        alert("Amount received should be less than total");
        $("#payments").val('');
    }
    }else if(payamount){
      if(payamount <= balance){
        var balance = total - payments - payamount;
      }
      else{
        alert("Amount received should be less than balance");
        $("#payamount").val('');
        var balance = total - payments; 
      }  
      $("#balance").val(balance);
    }else{
    }
    }
    $("#site_fkey").select2();
    $('#vehicle_master_fkey').select2();
    function checkdate() {
        var value = $('#total_cost').val();
        if (value < 1) {
            //alert('please enter a valid number');
            $('#total_cost').val('');
        }
    }

//validation form amount
    function findamount() {
        var rate = $('#total_cost').val();
//number format
        ///^[1-9][0-9\.]{0,15}$/
        ///^\d+$/
        if (rate.match(/^[1-9][0-9\.]{0,15}$/)) {
            if (rate < 1)
            {
                nofityMsg('Please enter a valid amount');
                $("#total_cost").val('');
            }
        }
        else {
            $("#total_cost").val('');
        }
      
    }

    function nofityMsg(msg) {//This function is created by ARUL P DAS on 3/6/2020
        $.notify(msg, {
            z_index: 1051,
            type: "danger"
        });
    }
    $(document).ready(function () {
        $("#starting_km").on('input', function () {
            var skm = parseInt($("#starting_km").val());
            var ekm = parseInt($("#ending_km").val());
            var rate_per_km = parseInt($("#rate_per_km").val());
            if (skm > 0 && ekm > 0) {
                var totalKM = ekm - skm;
                if (totalKM > 0) {
                    $("#total_km").val(totalKM);
                    $('#total_cost').val(totalKM * rate_per_km);
                    $("#balance").val(totalKM * rate_per_km);
                } else {
                    $("#total_km").val('0');
                    $('#total_cost').val('0');
                }
            } else {
                $("#total_km").val('0');
                $('#total_cost').val('0');
            }
        });
        $("#ending_km").on('input', function () {
            var skm = parseInt($("#starting_km").val());
            var ekm = parseInt($("#ending_km").val());
            var rate_per_km = parseInt($("#rate_per_km").val());
            if (skm > 0 && ekm > 0) {
                var totalKM = ekm - skm;
                if (totalKM > 0) {
                    $("#total_km").val(totalKM);
                    $('#total_cost').val(totalKM * rate_per_km);
                    $("#balance").val(totalKM * rate_per_km);
                } else {
                    $("#total_km").val('0');
                    $('#total_cost').val('0');
                }
            } else {
                $("#total_km").val('0');
                $('#total_cost').val('0');
            }
        });

        $("#starting_km").on('change', function () {
            var skm = parseInt($("#starting_km").val());
            var ekm = parseInt($("#ending_km").val());
            var rate_per_km = parseInt($("#rate_per_km").val());
            if (skm <= 0) {
                nofityMsg("Starting km should be greater than 0");
                $(this).val('');
                return false;
            }
            if (skm > 0 && ekm > 0) {
                if (skm < ekm) {
                    var totalKM = ekm - skm;
                    $("#total_km").val(totalKM);
                    $('#total_cost').val(totalKM * rate_per_km);
                    $("#balance").val(totalKM * rate_per_km);
                } else {
                    nofityMsg("Starting km greater than to Ending km");
                    $(this).val('');
                    $("#total_km").val('0');
                    $('#total_cost').val('0');
                }
            } else {
                $("#total_km").val('0');
                $('#total_cost').val('0');
            }
        });
        $("#ending_km").on('change', function () {
            var skm = parseInt($("#starting_km").val());
            var ekm = parseInt($("#ending_km").val());
            var rate_per_km = parseInt($("#rate_per_km").val());
            if (ekm <= 0) {
                nofityMsg("Ending km should be greater than 0");
                $(this).val('');
                return false;
            }
            if (skm > 0 && ekm > 0) {
                if (ekm > skm) {
                    var totalKM = ekm - skm;
                    $("#total_km").val(totalKM);
                    $('#total_cost').val(totalKM * rate_per_km);
                    $("#balance").val(totalKM * rate_per_km);
                } else {
                    nofityMsg("Ending km less than to Starting km");
                    $(this).val('');
                    $("#total_km").val('0');
                    $('#total_cost').val('0');
                }
            } else {
                $("#total_km").val('0');
                $('#total_cost').val('0');
            }
        });

        $('#starting_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
        }).on('changeDate', function () {
            var selectedstartdate = $("#starting_date").val();
            var esdt = new Date(selectedstartdate);
            var selectedenddate = $("#ending_date").val();
            var ecdt = new Date(selectedenddate);
            if (esdt > ecdt) {
                nofityMsg('Start Date should be less than End Date');
                $("#starting_date").val('');
            }
        });
        $('#ending_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
        }).on('changeDate', function () {
            var selectedenddate = $("#ending_date").val();
            var ecdt = new Date(selectedenddate);
            var selectedstartdate = $("#starting_date").val();
            var esdt = new Date(selectedstartdate);
            if (esdt > ecdt) {
                nofityMsg('End Date should be greater than Start Date');
                $("#ending_date").val('');
            }
        });

        //The below code is to get the rate_per_km of selected vehicle. by ***ARUL P DAS on 5/6/2020
        $('#vehicle_master_fkey').on('change', function () {
            $("#btn-submit").prop("disabled", true);
            $.ajax({
                url: livesite + 'VehicleExpenses/get_rate_per_km?veh_id=' + $(this).val(),
                type: 'POST',
                success: function (resp)
                {
                    var response = $.parseJSON(resp);
                    if (response.success == true) {
                        $('#rate_per_km').val(response.rate_per_km);
                        var skm = parseInt($("#starting_km").val());
                        var ekm = parseInt($("#ending_km").val());
                        if (skm > 0 && ekm > 0) {
                            var totalKM = ekm - skm;
                            if (totalKM > 0) {
                                $("#total_km").val(totalKM);
                                $('#total_cost').val(totalKM * response.rate_per_km);
                            } else {
                                $("#total_km").val('0');
                                $('#total_cost').val('0');
                            }
                        }
                    }
                    $("#btn-submit").prop("disabled", false);
                }
            });
        });
    });
</script>



