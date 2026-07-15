
<form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;margin-bottom:20px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>ProjectExpenses/return_save" >
<h1 class="page-header">Create New Credit Note</h1>
    <div class="col-md-12 bg-success" style="height:auto;"> 
        
            <h4 style="padding-left:20px;">Add Project Details</h4>

  <div class="divider"></div>
        <div class="">
            <div class="form-group">
                <div class="col-md-4">
                            <label for="vendor" class="col-sm-6 control-label">Project Name<label style="color:red;">*</label></label>
                            <div class="col-md-6">
                                <select id="vendor" name="vendor" class="form-control" required="required" onchange="filterContents();">
                                    <option value="">[--Select--]</option>
                                    <?php
                                    foreach ($site_list as $list) {
                                        foreach ($list as $l) {
                                            ?>
                                            <option value="<?php echo $l["site_pkey"] ?>"><?php echo $l["site_name"].' - '.$l["site_id"] ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>
                <div class="col-md-4">
                            <label for="beneficiary" class="col-sm-6 control-label">Beneficiary</label>
                            <div class="col-md-6">
                                <select id="beneficiary" name="beneficiary" class="form-control" onchange="filterType();">
                                    <option value="">[--Select--]</option>
                                    <?php
                                    foreach ($beneficiary as $ben) {
                                        //foreach ($ben as $b) {
                                            ?>
                                            <option value="<?php echo $ben["beneficiary"]["contact_id"] ?>"><?php echo $ben["beneficiary"]["company_name"]; ?></option>
                                            <?php
                                       // }
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>
                <div class="col-md-4">
                            <label for="in_time" class="col-sm-6 control-label">Expense Name<span class="star">*</span></label>
                            <div class="col-md-6">
                                <!--<input type="text"  class="form-control" value="" name="expense_type" id="expense_type" style="background:white;" placeholder="Enter Expense Type"  required="required">-->
                                <!--<input type="text" class="form-control" name="expense_type" id="expense_type" style="background:white;" placeholder="Enter Expense Type"  required="required" list="team_list">-->
                                <select id="expense_type" name="expense_type" class="form-control" required="required" onchange="filterId();">
                                    <option value="">[--Select--]</option>
                                    <!-- HERE SAVES THE EXPENSE TYPE PKEY INTO EXPENSE TABLE-->
                                    
                                    <?php
                                    foreach ($expense_type as $type) {
                                        foreach ($type as $t) {
                                            ?>
                                            <option value="<?php echo $t['expense_type_pkey']; ?>"><?php echo $t["expense_type_name"]; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>
                        
                        
   
            </div>
            <div class="form-group">
              <div class="col-md-4">
                    <label  class="col-md-6 control-label" >Request ID<label style="color:red;">*</label></label>
                    <div class="col-md-6">
                        <select id="expense_id" name="expense_id" class="form-control" required="required" onchange="get_details();">
                                    <option value="">[--Select--]</option>
                                    <?php
                                    foreach ($expense_list as $list) {
                                        foreach ($list as $l) {
                                            ?>
                                            <option value="<?php echo $l["emp_expenses_pkey"] ?>"><?php echo $l["expense_id"] ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                       </div>
                </div>
                 
                   </div>
        </div>

    </div>

    <div class="spacer-20"></div>

    <div class="col-md-12 bg-success" style="height:auto;">

        <h4 style="padding-left:20px;">Add Credit Note Details</h4>
        <div class="divider"></div>
        <div class="form-group">
                        <div class="col-md-4">
                            <label for="credit_note" class="col-sm-6 control-label">GST Credit Note No.</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="credit_note" id="credit_note"   placeholder="GST Credit Note No.">
                            </div> 
                        </div>
                    
                        <div class="col-md-4">
                            <label for="expense_date" class="col-sm-6 control-label">GST Credit Note Date<span class="star">*</span></label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" required="required" name="expense_date" id="expense_date"   placeholder="Select Date">
                            </div> 
                        </div>
                   
                        <div class="col-md-4">
                            <label for="ref_bill_no" class="col-sm-6 control-label">Reference Bill No.</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="ref_bill_no" id="ref_bill_no"   placeholder="Reference Bill No.">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-4">
                            <label for="credit_date" class="col-sm-6 control-label">Reference Bill Date</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="credit_date" id="credit_date"   placeholder="Select Date">
                            </div> 
                        </div>
                   
                        <div class="col-md-4">
                            <label for="expenses_amount" class="col-sm-6 control-label">Purchase Return Amount<span class="star">*</span></label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" required="required" max="<?php //echo $exp;?>" onchange="add();" class="form-control" value="" name="expenses_amount" id="expenses_amount">
                            </div> 
                        </div>
                    
                         <div class="col-md-4">
                            <label for="cgst" class="col-sm-6 control-label">CGST</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" class="form-control" max="" onchange="add();" value="" name="cgst" id="cgst" >
                            </div> 
                        </div>
                 </div>
               <div class="form-group">
                        <div class="col-md-4">
                            <label for="sgst" class="col-sm-6 control-label">SGST</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" class="form-control"  max="" onchange="add();" value="" name="sgst" id="sgst">
                            </div> 
                        </div>
               
                        <div class="col-md-4">
                            <label for="igst" class="col-sm-6 control-label">IGST</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" class="form-control" max="" onchange="add();" value="" name="igst" id="igst">
                            </div> 
                        </div>
             
                        <div class="col-md-4">
                            <label for="total" class="col-sm-6 control-label">Total</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" class="form-control" max="" value="" name="total" id="total">
                            </div> 
                        </div>
                    </div>
                   <div class="form-group">
                        <div class="col-md-4">
                            <label for="payment" class="col-sm-6 control-label">Payment Return</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01"  min="0" onchange="minus();" class="form-control" max="" value="" name="payment_total" id="payment_total">
                            </div> 
                        </div>
               
                        <div class="col-md-4">
                            <label for="return_date" class="col-sm-6 control-label">Payment Return Date</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="return_date" id="return_date"   placeholder="Select Date">
                            </div> 
                        </div>
                    </div>
                
<!--        <div class="form-group">
           <div class="col-md-4">
                            <label for="payment" class="col-sm-6 control-label">Payment</label>
                            <div class="col-md-6">
                                <input type="number" step="0.001"  onchange="get_balance();" min="0" class="form-control" value="<?php echo isset($data['0']['emp_expense_details']['payment'])?$data['0']['emp_expense_details']['payment']:'';?>" name="exp_payment" id="exp_payment">
                            </div> 
                        </div>
                        <div class="col-md-4">
                            <label for="balance" class="col-sm-6 control-label">Balance</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" readonly="readonly" class="form-control" value="<?php echo isset($data['0']['emp_expense_details']['balance'])?$data['0']['emp_expense_details']['balance']:'';?>" name="exp_balance" id="exp_balance">
                            </div> 
                        </div> 
        </div>-->
<!--        <div class="col-md-2  pull-right">
                <input type="hidden" name="emp_expenses_total" id="emp_expenses_total" value="">
                <input type="hidden" name="emp_expenses_pkey" id="emp_expenses_pkey" value="">
                <button type="submit" id="btn-submit" class="btn btn-primary pull-right "  style="margin-right:15px;width: 145px;"><li class="fa fa-arrow-down"></li> Add to List</button>
          </div>-->
<!--        <div class="spacer-20"></div>-->
       
<!--        <div class="divider"></div>-->
       
            <div class="modal-footer">
            
            <div id="dispatch">
                <input type="hidden" name="pay_status" value="">
                <input type="hidden" name="emp_expense_fkey" id="emp_expense_fkey" value="">
                <input type="hidden" name="expense_details_pkey" id="expense_details_pkey" value="">
                <input type="hidden" name="paid_total" id="paid_total" value="">
                <button type="submit" id="btn-submit" class="btn btn-primary pull-right "  style="margin-right:15px;">Submit</button>
                <input type="button" id="btn-remove" value="Cancel" class="btn btn-danger" style="margin-right: 20px;">
            </div>
<!--            <div class="spacer-20"></div>-->
        </div >
        </div>
        

          
</form>
<br>
<!--<div id="dispatch">
                <input type="button" id="requestsubmit" onclick="requestsubmit();" value="Submit Request" class="btn btn-primary pull-right" style="width: 145px;margin-right: 40px;">
                <input type="button" id="btn-remove" value="Remove" class="btn btn-danger" style="width:140px;">
            </div>-->
<script>
 function add(){
         var amt = parseFloat($('#expenses_amount').val());
         var sgst = parseFloat($('#cgst').val());
         var cgst = parseFloat($('#cgst').val());
         var igst = parseFloat($('#igst').val());
         if(amt> 0 && sgst > 0){
         var total = amt + sgst + cgst;
         $("#igst").val('');
         $("#sgst").val(sgst);
         }else if(amt> 0 && igst > 0){
         var total = amt + igst;
         $("#sgst").val('');
         $("#cgst").val('');
         $("#igst").val(igst);
         }else if (amt> 0 ){
           var total = amt; 
           $("#sgst").val('');
         }else{
          //var total = amt + sgst + cgst + igst;   
         }
         
         total = parseInt(Math.round(total));
         //$("#sgst").val(sgst);
         $("#total").val(total);
        
    }
      function minus(){
         var total = parseFloat($('#total').val());
         var payment_total = parseFloat($('#payment_total').val());
         var payment = parseFloat($('#payment').val());
         if(total> 0 && payment > 0){
         var balance = total - payment - payment_total;
         }else if(total> 0){
          var balance = total - payment_total;   
         }
         balance = parseFloat(balance);
         if(balance < 0){
         // $("#payment").val('');
         }else{
         $("#balance").val(balance);
         }
    }
     function check(){
         var total = parseFloat($('#emp_expenses_total').val());
         var payment = parseFloat($('#payment').val());
       if(payment > total){
           $("#payment").val('');
           minus();
           alert("Payment amount is greater than total amount!!!");
           
     } 
    }
    function get_balance(){
         $("#exp_balance").val('');
         var total = parseFloat($('#total').val());
         var payment = parseFloat($('#exp_payment').val());
         console.log(payment);
         console.log(total);
         if(total> 0 && payment > 0){
         var balance = total - payment;
         }else if(total> 0){
          var balance = total;   
         }
        // balance = parseFloat(balance);
        if(balance < 0){
            alert("Payment is greater than balance");
        }else{
         $("#exp_balance").val(balance);
        }
//         if(balance > 0){
//          $("#payment_status").val("Pending");   
//         }else{
//          $("#payment_status").val("Completed");    
//         }
    }
  
    function removedaata(index, id) {

        // alert(id);

        if (id) {
            var r = confirm("Do You Want To Remove The Selected Item?");
            if (r == true) {
                var pid = $("#emp_expenses_pkey").val();
                $.ajax({
                    url: livesite+'ProjectExpenses/deleteorder/' + id,
                    success: function (resp) {
                        loadtable(pid, 1);
                        var total = $.parseJSON(resp).total;
                        console.log(total);
                        $("#emp_expenses_total").val(total);
                        
                        $.notify($.parseJSON(resp).msg, {
                            type: 'danger',
                            allow_dismiss: false
                        });
                        minus();
                    }
                });
            } else
            {
                alert("cancelled");
            }
        }


    }
    $(document).ready(function () {
    $('#credit_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            //startDate: '+1d',
        });
   $('#expense_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            //startDate: '+1d',
        });
    
    $('#return_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            //startDate: '+1d',
        });
   
        });
    $('#form-user-master').on('submit', function (event) {
            event.preventDefault();
             if($('#expense_id').val() == ''){
                if ($("#expense_id").next(".validation").length == 0) // only add if not added
                 {
                $('#expense_id').parent().append("<div class='validation' style='color:red;'>Please select request id.. </div>");
                
                 }
                 return false;
            }else{
                $("#expense_type").next(".validation").hide();
            }
            if($('#vendor').val() == ''){
                if ($("#vendor").next(".validation").length == 0) // only add if not added
                 {
                $('#vendor').parent().append("<div class='validation' style='color:red;'>Please select project name </div>");
                
                 }
                 return false;
            }else{
                $("#vendor").next(".validation").hide();
            }
             if($('#expense_type').val() == ''){
                if ($("#expense_type").next(".validation").length == 0) // only add if not added
                 {
                $('#expense_type').parent().append("<div class='validation' style='color:red;'>Please select expense type.. </div>");
                
                 }
                 return false;
            }else{
                $("#expense_type").next(".validation").hide();
            }
            if($('#expense_date').val() == ''){
                if ($("#expense_date").next(".validation").length == 0) // only add if not added
                 {
                $('#expense_date').parent().append("<div class='validation' style='color:red;'>Please select GST Credit Note Date. </div>");
                
                 }
                 return false;
            }else{
                $("#expense_date").next(".validation").hide();
            }

            
            if($('#expenses_amount').val() == ''){
                if ($("#expenses_amount").next(".validation").length == 0) // only add if not added
                 {
                $('#expenses_amount').parent().append("<div class='validation' style='color:red;'>Please enter Purchase Return Amount. </div>");
                
                 }
                 return false;
            }else{
                $("#expense_amount").next(".validation").hide();
            }
   
            $('#form-user-master').ajaxSubmit({
                success: function (resp) {
                    var msg = $.parseJSON(resp).msg;
                    $.notify(msg, {
                        type: 'success',
                        allow_dismiss: true
                    });
                     refresh();
                      $('#att_table_verified').datagrid('load'); 
                }

            });

        });
   
    function refresh() {
//        var r = confirm("Do you want to refresh? ")
//            if (r == true) {
        $("#emp_expenses_pkey").val('');
        $("#form-user-master").find('input:text, input:password, input:file, select, textarea,hidden,search').val('');
        $("#form-user-master").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
       
        $('#dispatch').hide();
        $('#btn-refresh').fadeOut();
        $('#newreqeuest').html('');
        $('#att_table_verified').datagrid('load');
//            }
    }
 function refresh_item() {
        //$("#emp_fkey").val('');
        $("#expense_type").val('');
        $("#category").val('');
        //$("#exp_date").val('');
        $("#expense_amount").val('');
        $("#cgst").val('');
        $("#sgst").val('');
        $("#igst").val('');
        $("#total").val('');
//        $("#gst_bill_no").val('');
//        $("#gst_bill_status").val('');
        $("#exp_payment").val('');
        $("#exp_balance").val('');
//        $("#payment_status").val('');
        $("#related_party").val('');
        $("#image").val('');
    }
    $("#btn-remove").click(function () {
       // if (id) {
            var r = confirm("Do You Want To Remove The Credit Note Request?")
            if (r == true) {
                        refresh();
                        $.notify("Credit Note Request Removed.", {
                            type: 'danger',
                            allow_dismiss: false
                        });
            } else
            {
                alert("Cancelled");
            }
//         }else{
//            alert("No Expense for remove!!!");
//        }

    });
     //filtter using branch 
    function filterCategory(type)
    {
        $("#category").val('');
        var type = $('#expense_type').val();
        $("#category").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ProjectExpenses/category/" + type,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
    function filterContents(){
        //filterProject(type);
        filterBeneficiary();
        filterExpense();
    }
    
      //filter using id 
//    function filterProject(type)
//    {
//        $('#vendor').val(''); 
//        var type = $('#expense_id').val();
//        $("#vendor").select2(
//                {
//                    //closeOnSelect:false,
//                    placeholder: "Select",
//                    allowClear: true,
//                    ajax: {
//                        url: livesite + "ProjectExpenses/project/" + type,
//                        dataType: 'json',
//                        delay: 250,
//                        data: function (params) {
//                            return {
//                                q: params.term, // search term
//                                page: params.page
//                            };
//                        },
//                        processResults: function (data, params) {
//                            params.page = params.page || 1;
//                            return {
//                                results: data.items,
//                                pagination: {
//                                    more: (params.page * 30) < data.total_count
//                                }
//                            };
//                        }
//                    }, 
//                    escapeMarkup: function (markup) {
//                        return markup;
//                    }
//                });
//    }
    
      //filter using id 
    function filterBeneficiary()
    {
        $('#expense_id').val('');
        $('#beneficiary').val(''); 
        var type = $('#vendor').val();
        $("#beneficiary").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ProjectExpenses/beneficiary/" + type,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    }, 
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
         //filter using id 
    function filterExpense()
    {
        $('#expense_type').val(''); 
        var type = $('#vendor').val();
        $("#expense_type").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ProjectExpenses/expense_type/" + type,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    }, 
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
    function filterType()
    {
        $('#expense_id').val('');
        $('#expense_type').val(''); 
        var type = $('#vendor').val();
        var ben = $('#beneficiary').val(); 
        $("#expense_type").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ProjectExpenses/expense_typelist/" + type + '/' + ben,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    }, 
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
    function filterId()
    {
        $('#expense_id').val(''); 
        var vendor = $('#vendor').val();
        var ben = $('#beneficiary').val(); 
        var type = $('#expense_type').val(); 
        $("#expense_id").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ProjectExpenses/request_id/" + vendor + '/' + type + '/' + ben,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    }, 
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
     function  get_details() {
          var expense_id = $('#expense_id').val();
          var expense_type = $('#expense_type').val();
        //$("#table_appnd").html('<li style="    font-size: -webkit-xxx-large;" class="fa fa-spinner fa-spin"></li><br>Loading Data....');
        $.ajax({
            url: livesite+ 'ProjectExpenses/get_details/' + expense_id + '/' + expense_type,
            success: function (response) {
                console.log(response);
                var amt = $.parseJSON(response).a;
                var cgst = $.parseJSON(response).b;
                var sgst = $.parseJSON(response).c;
                var igst = $.parseJSON(response).d;
                var total = $.parseJSON(response).e;
                var payment_total = $.parseJSON(response).payment_total;
                var payment_status = $.parseJSON(response).payment_status;
                var emp_expense_fkey = $.parseJSON(response).emp_expense_fkey;
                var expense_details_pkey = $.parseJSON(response).expense_details_pkey;
                var paid_total = $.parseJSON(response).paid_total;
                //var val1 = $("#expenses_amount").val();
                $("#expenses_amount").val(amt);
                $("#expenses_amount").attr( "max",amt);
               // set_max_amt(amt);
                //$("#expenses_amount").val(Math.max(Math.min(val1, amt), 0));
                //var val2 = $("#cgst").val();
                $("#cgst").val(cgst);
                $("#cgst").attr( "max",cgst);
                //$("#cgst").val(Math.max(Math.min(val2, cgst), 0));
                //var val3 = $("#sgst").val();
                $("#sgst").val(sgst);
                $("#sgst").attr( "max",sgst);
                //$("#sgst").val(Math.max(Math.min(val3, sgst), 0));
                //var val4 = $("#igst").val();
                $("#igst").val(igst);
                $("#igst").attr( "max",igst);
                //$("#igst").val(Math.max(Math.min(val4, igst), 0));
                //var val5 = $("#total").val();
                $("#total").val(total);
                $("#total").attr( "max",total);
                //$("#total").val(Math.max(Math.min(val5, total), 0));
                $("#total").attr( "max",payment_total);
                $("#payment_status").val(payment_status);
                $("#emp_expense_fkey").val(emp_expense_fkey);
                $("#expense_details_pkey").val(expense_details_pkey);
                $("#paid_total").val(paid_total);
            }
        });

    }
  function set_max_amt(amt){
    $("#expenses_amount").max = amt;
    }

</script>