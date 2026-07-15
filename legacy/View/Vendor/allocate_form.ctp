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
            <form class="form-horizontal" id="emp_expance" action="<?php echo $this->webroot; ?>Uniform/allocate_save" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Employee Name<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select class="form-control" name="emp_fkey" id="emp_fkey">
                                    <?php foreach($emp_arr as $val){ ?>
                                    <option value="<?php echo $val['emp_details']['emp_pkey']; ?>"><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group box box-body" id="allocate">
                        <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Add Items<span class="star">*</span></label>
                            <div class="col-md-7">
                                <a class="btn btn-primary pull-right" onclick="appendafter();"><li class="fa fa-plus"></li></a>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Value<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item']['value'])?$item_details['0']['item']['value']:''; ?>" name="value" id="value" placeholder="Enter item value"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Date Allocated<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['date_allocated'])?$item_details['0']['item_purchase']['date_allocated']:''; ?>" name="date_allocated" id="date_allocated" placeholder="Enter Date Purchased"  required="required" >
                            </div> 
                        </div>
                    </div>
<!--                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Allocate<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['date_purchased'])?$item_details['0']['item_purchase']['date_purchased']:''; ?>" name="date_purchased" id="date_purchased" placeholder="Enter Date Purchased"  required="required" >
                            </div> 
                        </div>
                    </div>-->
                    
                    <div class="modal-footer">
                        <input type="hidden" required="required" class="form-control" value="<?php echo isset($item_details['0']['item']['item_pkey'])?$item_details['0']['item']['item_pkey']:''; ?>"name="emp_expenses_pkey">
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
    var index = 1;
  function appendafter(){
      $('#allocate').append('<div id="allocate_'+index+'"></div>');
      
      $('#allocate_'+index).load(livesite + 'uniform/load_qty');
      index += 1;
  }
  
  function removei(s){
      $(s).parent().parent().parent().html("");
  }
 
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
        $('#date_allocated').datepicker({
            format: 'yyyy-mm-dd',
            autoclose:true, 
        })
        $("#out_date").inputmask("yyyy-mm-dd");
    });
</script>



