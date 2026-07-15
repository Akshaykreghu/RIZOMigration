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
            <h4 class="modal-title"> Vendor Master</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <form class="form-horizontal" id="emp_expance" action="<?php echo $this->webroot; ?>Vendor/Master_save" method="POST" data-toggle="validator" role="form" >
                <div class="modal-body">
                    <div class="col-sm-12">

                    <div class="row"><div class="col-sm-6 form-group">
                                 <label for="inputName" class="control-label">Company</label>
                                <input type="text" placeholder="Enter Company Name Here.." class="form-control" required="required" name='company_name' id='company_name'>
                            </div> 
                            <div class="col-sm-6 form-group">
                                <label for="inputName" class="control-label">Email</label>
                                <input type="email" placeholder="Enter Company Email Here.." class="form-control" required="required" name="email" id="email">
                            </div> 
                            </div>
 <div class="row"><div class="col-sm-6 form-group">
                                <label>Phone</label>
                                <input type="text" placeholder="Enter Company Phone Here.." class="form-control" name="phone">
                            </div> 
                            <div class="col-sm-6 form-group">
                                <label for="inputName" class="control-label">Fax</label>
                                <input type="text" placeholder="Enter Company Fax Here.." class="form-control" name="fax">
                            </div> 
                            </div>
                             <div class="row"><div class="col-sm-4 form-group">
                                <label for="inputName" class="control-label">Pan Number</label>
                                <input type="text" placeholder="Enter Company PAN Here.." class="form-control" name="pan_no">
                            </div> 
                            <div class="col-sm-4 form-group">
                                <label for="inputName" class="control-label">GST Number</label>
                                <input type="text" placeholder="Enter Company GST Here.." class="form-control" name="cst">
                            </div> 
                            <div class="col-sm-4 form-group">
                                <label for="inputName" class="control-label">TAN Number</label>
                                <input type="text" placeholder="Enter Company TAN Here.." class="form-control" name="tin">
                            </div> 
                            </div>
                             <div class="row">
                            <div class="form-group col-sm-10">
                            <label for="inputName" class="control-label">Address</label>
                            <textarea placeholder="Enter Company Address Here.." rows="3" class="form-control" name="address"></textarea>
                        </div>  </div>
                           <h3>Contact Person Details</h3><hr>
                        <div class="row">
                           <div class="col-sm-6 form-group">
                                <label> First Name</label>
                                <input type="text" placeholder="Enter Contact Person First Name Here.." class="form-control" name="first_name">
                            </div> 
                            <div class="col-sm-6 form-group">
                                <label>Last Name</label>
                                <input type="text" placeholder="Enter Contact Person Last Name Here.." class="form-control" name="last_name">
                            </div>
                        </div>  

                         <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Gender</label> 
                                <select class="form-control" name="c_gender">
                                <option value="m">Male</option>
                                <option value="f">Female</option>
                                </select>
                                <!-- <input type="text" placeholder="Enter City Name Here.." > -->
                            </div>  
                            
                            <div class="col-sm-6 form-group">
                                <label>Mobile Number</label>
                                <input type="text" placeholder="Enter Contact Person Mobile number Here.." class="form-control" name="c_mob_no">
                            </div>      
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Designation</label>
                                <input type="text" placeholder="Enter Contact Person Designation Here.." class="form-control" name="c_designation">
                            </div>  
                             <div class="col-sm-6 form-group">
                                <label>Email Address</label>
                                <input type="email" placeholder="Enter Contact Person Email Here.." class="form-control" name="c_email">
                            </div>      
                            
                        </div>                      
                 
                    </div>                
                        

                       
                       
                    <div class="modal-footer">
                        <input type="hidden" required="required" name="relationship" value="vendor">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="btn-submit" id="btn-submit" class="btn btn-success">Save</button>

                    </div>



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
            autoclose:true, 
            onSelect: function (selected) {
                var esdt = new Date(selected);
                var selectedenddate = $("#out_date").val();
                var ecdt = new Date(selectedenddate);
                if (esdt > ecdt) {
                    alert('Expected In Date Should Be Less Than Expected Out Date');
                    $("#in_date").val('');
                }

            }
        })
        $('#out_time').datepicker({
            format: 'yyyy-mm-dd',
            onSelect: function (selected) {
                var ecdt = new Date(selected);
                var selectedstartdate = $("#in_date").val();
                var esdt = new Date(selectedstartdate);
                if (esdt > ecdt) {
                    alert('Expected Out Date Should Be Greater Than Expected In Date');
                    $("#out_date").val('');
                }

            }
        })
        $("#out_date").inputmask("yyyy-mm-dd");
    });
</script>



