<script>
//    jQuery.validator.setDefaults({
//  debug: true,
//  success: "valid"
//});
//       $( "#emp_expance" ).validate({
//  rules: {
//    expenses_amount: {
//      required: true,
//      number: true
//    }
//  }
//});
    $.validate({
        form: '#emp_expance'
    });
    var options = {
        success: function (resp) {
            $('#modalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };


    $('#emp_expance').on('submit', function (event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#emp_expance').ajaxSubmit(options)
        }
    });


</script>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> Uniform Master</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <form class="form-horizontal" id="emp_expance" action="<?php echo $this->webroot; ?>Uniform/save_Purchase" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Item Name<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select class="form-control" name="item_fkey" id="item_fkey">
                                    <?php foreach($arr_att as $val){ ?>
                                    <option value="<?php echo $val['item_master']['item_pkey']; ?>"><?php echo $val['item_master']['item_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">vendor<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['vendor'])?$item_details['0']['item_purchase']['vendor']:''; ?>" name="vendor" id="vendor" placeholder="Enter vendor"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Item Qty<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['qty'])?$item_details['0']['item_purchase']['qty']:''; ?>" name="qty" id="qty" placeholder="Enter item Description"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Date Purchased<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['date_purchased'])?$item_details['0']['item_purchase']['date_purchased']:''; ?>" name="date_purchased" id="date_purchased" placeholder="Enter Date Purchased"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">P O Number<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['PO_number'])?$item_details['0']['item_purchase']['PO_number']:''; ?>" name="PO_number" id="PO_number" placeholder="Enter PO Number"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Branch<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select class="form-control" name="branch_code" id="branch_code">
                                    <?php foreach($arr_branchs as $val){ ?>
                                    <option value="<?php echo $val['Units']['branch_code']; ?>"><?php echo $val['Units']['branch_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" required="required" class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['item_pkey'])?$item_details['0']['item_purchase']['item_pkey']:''; ?>"name="emp_expenses_pkey">
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
 
    function checkdate() {
        var value = $('#expenses_amount').val();
        if (value < 1) {
            //alert('please enter a valid number');
            $('#expenses_amount').val('');
        }
    }
//salary  slip check    
    function findextingsalary() {
      //  alert('hi');
        var aff_date = $('#affected_date').val();
        var emp_id = $('#emp_id').val();
        // alert(emp_id);
        //alert(month);
        var url = 'EmployeeExpenses/salarycheck';
//value passiing ajax   
        $.ajax({
           // alert('hi');
            url: url,
            type: 'post',
            data: {
                date: aff_date,
                emp_id: emp_id,
//                     //   active:active
            },
//success time hide                     
            success: function (resp) {
                //alert(resp);
                var json_obj = $.parseJSON(resp);

                if (json_obj.rows.length > 0) {
                    $.notify(json_obj.msg,{  
                        type: 'danger',
                        allow_dismiss: false
                    });
                     $("#btn-submit").hide();
                     //$("#btn-submit").hide();
                    //$('#btn-submit').attr(hide);
                    $("#expenses_amount").val('');
                    $('#expenses_amount').attr('readonly', true);
                    $("#remarks").val('');
                    $('#remarks').attr('readonly', true);

                }
//unhide                  
                else {
                   // $.notify("BOOM!", "error");
                    $("#btn-submit").show();
                    //$("#expenses_amount").val('');
                    $('#expenses_amount').attr('readonly', false);
                    $('#remarks').attr('readonly', false);
                }
            }
        });
    }
//validation form amount
    function findamount() {
        var rate = $('#expenses_amount').val();
//number format
        ///^[1-9][0-9\.]{0,15}$/
        ///^\d+$/
            if(rate.match(/^[1-9][0-9\.]{0,15}$/)){
               if (rate < 1)
        {
            alert('Please Enter A Valid Amount')
            $("#expenses_amount").val('');
        }
            }
            else{
                 $("#expenses_amount").val('');
                // alert('hi');
            }
        
    }

    $(document).ready(function () {
        //$("#pincode").inputmask("999");
        $('#attendanceuploadtable').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                alert("Attendance Uploaded Successfully");
                closeModal('att_table');
            }
        };

        // bind to the form's submit event 
        $('#attendanceuploadtable').submit(function () {
            $(this).ajaxSubmit(options);
            return false;
        });

        $('#affected_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose:true });
        $('#date_purchased').datepicker({
            format: 'yyyy-mm-dd',
            autoclose:true
            }
        );
        $("#out_date").inputmask("yyyy-mm-dd");
    });
</script>



