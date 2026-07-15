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
            $('#largeModalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };


    $('#emp_expance').on('submit', function (event) {
        event.preventDefault();
        $sum = $("#values").val();
//        var dateFormat = "yyyy-mm-dd",
//        var element = $("#date_allocated").val();
//        $date = $.datepicker.parseDate( dateFormat, element.value );
//        alert($date);
      $( "#date_allocated" ).datepicker({
      onSelect: function(dateText, inst) { alert("Working"); }
      });
        if($sum != 0){
        loadsums();
       //var s = isempty();
       //alert(s);
        if (confirm(" Do You Want  To Save The Form")) {
            $('#emp_expance').ajaxSubmit(options);
        }
       }
       else{
           alert("Total Value should not be zero.");
         loadsums();  
       }
    });
   

</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> Uniform Allocation</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <form class="form-horizontal" id="emp_expance" action="<?php echo $this->webroot; ?>Uniform/allocate_emp" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Employee Name<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select class="js-example-basic-single" style="width: 100%;" name="emp_fkey" id="emp_fkey">
                                    <option value=""> Select</option>
                                    <?php foreach($emp_arr as $val){ ?>
                                    <option value="<?php echo $val['emp_details']['emp_pkey']; ?>"><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name'].' - '.$val['emp_proff']['emp_company_id']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Store<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select class="js-example-basic-single" name="store_fkey" style="width: 100%;" id="store_fkey" onchange="clearallocation();" >
                                    <option> Select</option>
                                    <?php foreach($store_arr as $val){ ?>
                                    <option value="<?php echo $val['store_master']['store_master_pkey']; ?>"><?php echo $val['store_master']['store_code'].' -  '.$val['store_master']['store_location']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Date Allocated<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['date_allocated'])?$item_details['0']['item_purchase']['date_allocated']:''; ?>" name="date_allocated" id="date_allocated" placeholder="Enter Date" onchange="clearallocation();" required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group box box-body" id="allocate">
                        <div class="form-group">
                            <a class="btn btn-primary pull-right" onclick="appendafter();"><li class="fa fa-plus"></li></a>
                        
                    </div>
                        <div id="errormsg" style="color: red;text-align: center;"></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Value<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="number" min="1" readonly="readonly" class="form-control" value="<?php echo isset($item_details['0']['item']['value'])?$item_details['0']['item']['value']:''; ?>" name="value" id="values" placeholder="Enter item value"  required="required" >
                            </div>
                            <div class="col-md-1">
                                <a onclick="loadsums();" class="btn-default pull-right"><li class="fa fa-refresh"></li></a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Discount<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="number" class="form-control" onchange="loadsums();" value="" min="0" name="Discount" id="Discount" placeholder="Enter Dsicount value"  >
                            </div>
                            <div class="col-md-1">
                                <a onclick="loadsums();" class="btn-default pull-right"><li class="fa fa-refresh"></li></a>
                            </div>
                        </div>
                    </div>
                    
                    <legend>Uniform EMI Detail</legend>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Tenure<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="number" min="1" onchange="findendmonth();" class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['date_allocated'])?$item_details['0']['item_purchase']['date_allocated']:1 ; ?>" name="tenures" id="tenure" placeholder="Tenure of EMI"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">Start Date<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text" onchange="findendmonth();" class="form-control" value="" name="emi_start_date" id="emi_start_date" placeholder="Emi Start date"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">End Date<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text" disabled="disabled" onchange="loadsums();" class="form-control" value="" name="emi_end_date" id="emi_end_date" placeholder="Emi End date"  required="required" >
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-4 control-label">EMI<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text" readonly="true"  class="form-control" value="<?php echo isset($item_details['0']['item_purchase']['date_allocated'])?$item_details['0']['item_purchase']['date_allocated']:''; ?>" name="emiss" id="emiss" placeholder="EMI Per Months"  required="required" >
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
<style>
    .select2-container--default .select2-search__field {
        pointer-events: auto !important;
        cursor: text !important;
    }
</style>
<script type="text/javascript">
   $(document).ready(function() {
   // $('.js-example-basic-single').select2();
        $('#emp_fkey').select2({
            width: '100%',
            placeholder: "Search employee...",
            allowClear: true,
            dropdownParent: $('#emp_fkey').parent()
        }).on('select2:open', function() {
            document.querySelector('.select2-search__field').focus();
        });
        
        $('#store_fkey').select2({
            width: '100%',
            placeholder: "Select a store",
            allowClear: true,
            dropdownParent: $('#store_fkey').parent()
        }).on('select2:open', function() {
            document.querySelector('.select2-search__field').focus();
        });
        //edited by athira on 27-03-2025
        $('#attendanceuploadtable').parsley();

        // $('.js-example-basic-single').select2();
        $('#largeModalForm').on('shown.bs.modal', function() {
            $(this).find('.js-example-basic-single').each(function() {
                if ($.fn.select2 && $(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2('destroy'); // Destroy existing instance if already initialized
                }
                // Reinitialize Select2 with modal-specific dropdown positioning
                $(this).select2({
                    dropdownParent: $('#largeModalForm'),
                    // Prevent dropdown from being clipped
                });
            });
        });
        //end
    });
    var index = 1;
  function appendafter(){
      if(isempty() == false){
          return false;
      }
      $('.items_disable').prop("disabled","disabled");
      $('#allocate').append('<div class="allocate" id="allocate_'+index+'"></div>');
      var date_allocated = $('#date_allocated').val();
      var store_fkey = $('#store_fkey').val();
      
      if(store_fkey == 'Select'){
          alert("Please Select Store");
          return false;
      }
      if(date_allocated == ''){
          alert("Please Select Date");
          return false;
      }
      $('#allocate_'+index).load(livesite + 'uniform/load_qty/'+store_fkey+'/'+date_allocated);
      index += 1;
  }
  function clearallocation(){
      $('.allocate').html('');
      $('#Discount').val('');
      $('#values').val('');
  }
//  function checkqty(s){
//        var qty = $(s).val();
//        var bal = $(s).siblings().val();
//        var rat = $(s).parent().parent().siblings().find('#item_value').val();
//        //alert(qty);
//        //alert(bal);
//        if(parseInt(qty) > parseInt(bal)){
//            alert("Insufficient Stock");
//            $(s).val('');
//            
//        }else{
//        var sum = qty * rat;    
//        var rat = $(s).parent().parent().siblings().find('.item_bal').val(sum);    
//        loadsums();
//    }
//    }
 //edited by megha on 5_6_19
  function checkqty(s){
        var qty = $(s).val();
        var bal = $(s).siblings().val();
        var rat = $(s).parent().parent().siblings().find('#item_value').val();
         var item_pkey = $(s).parent().parent().parent().siblings().find('#item_pkey').val();
        //var item_pkey = $('#item_pkey').val();
        var item_fkey = item_pkey.split("-", 1); 
        
        var date_allocated = $('#date_allocated').val();
        var store_fkey = $('#store_fkey').val();
      
        $.ajax({
            url: livesite + 'uniform/finditem_qty/' + date_allocated + '/' + store_fkey+'/'+item_fkey,
            success: function (response) {
             var rat = $(s).parent().parent().siblings().find('#item_value').val();
             var data = $.parseJSON(response);
             if(parseInt(qty) > data){
                //alert("Insufficient Stock");
               $("#errormsg").html("You don't have enough stock for this transaction. Please select another transaction date / lesser quantity and try again!!!");
               $(s).val('');
               $("#values").val('');
               $(s).parent().parent().siblings().find('.item_bal').val('');
             }else{
               $("#errormsg").html("");
               var sum = qty * rat;    
               var rat = $(s).parent().parent().siblings().find('.item_bal').val(sum);  
               loadsums();
             }
            }
        });
       
    
    }
    function loadsums(){
        var value_sum = 0;
        var emis = 0;
        var tenures = 0;
        $('.item_qty').each(function(){
            var value = parseInt($(this).parent().parent().siblings().find('#item_value').val());
            //alert(value);
            var qty = parseInt($(this).val());
            ////alert(qty);
            value_sum = parseInt(value_sum) + value*qty ; 
        });
        var Discount = $('#Discount').val();
       if(Discount > value_sum){
        alert("Discount value cannot exceed total value.");
        $('#Discount').val('');
        Discount = $('#Discount').val();
         }
        if(isNaN(value_sum)){
            value_sum = 0;
        }else{
        }
        $('#values').val(value_sum - Discount);
        tenures = parseInt($('#tenure').val());
        emis = (value_sum - Discount)/tenures;
        //alert(emis);
        if(isNaN(emis)){
            emis = 0;
        }else{
           
        }
        $('#emiss').val(Math.round(emis,2));
    }
    //edited by megha on 5_6_19
//    function loadsums(){
//        var value_sum = 0;
//        var emis = 0;
//        var Discount = $('#Discount').val();
//        var tenures = 0;
//        //alert(value_sum);
//        $('.item_qty').each(function(){
//            var value = parseInt($(this).parent().parent().siblings().find('#item_value').val());
//            //alert(value);
//            var qty = parseInt($(this).val());
//            ////alert(qty);
//            value_sum = parseInt(value_sum) + value*qty ; 
//        });
//        if(isNaN(value_sum)){
//            value_sum = 0;
//        }else{
//            
//        }
//        $('#values').val(value_sum - Discount);
//        tenures = parseInt($('#tenure').val());
//        emis = (value_sum - Discount)/tenures;
//        //alert(emis);
//        if(isNaN(emis)){
//            emis = 0;
//        }else{
//           
//        }
//        $('#emiss').val(Math.round(emis,2));
//    }
    
    function isempty(){
        var empty = true;
        $('.input').each(function(){
           if($(this).val()==""){
              empty =false;
              alert("Please fill all Fields First");
              return false;
            }
         });
         return empty;
    }
   
    function findendmonth(){
        var emp_fkey = $('#emp_fkey').val();
        //alert(emp_fkey);
        if($('#emp_fkey').val() == ""){
          alert("Please Select Employee");
          $('#emi_start_date').val('');
          //$('#tenure').val('');
          return false;
        }
        
        var values = $('#values').val();
        if(values === ''){
          alert("Please Select atleast one item.");
          $('#emi_start_date').val('');
          //$('#tenure').val('');
          e.stopImmediatePropagation();
          return false;
        }
        var start_month = $('#emi_start_date').val();
        var tenure = $('#tenure').val();
        $.ajax({
           url: livesite+ 'Uniform/getendmonth/',
           type:"post",
              statusCode: {
                404: function() {
                  alert( "page not found" );
                }
              },
              data:{start_month:start_month,tenure:tenure},
              success: function (resp) {
                  var item_value = parseInt(resp);
                  $('#emi_end_date').val(resp);
                         
                     }
            });
        loadsums();
    }
    
    function calculate_emi(s){
        
    }
    
   function loadbalance(s){
       $("#errormsg").html("");
        //alert($(s).val());
        var item_master_fkey =  $(s).val().substring(0, $(s).val().indexOf("-"));
        
        var v=  $(s).val();
        
        $('.item').each(function(){
            
            var qty = $(this).val();
            if(qty == v){
                alert("Item already selected, Please update the quantity if you want ");
                $(s).val('')
                return false;
            }
            //alert(qty); 
        });
        
        $(s).siblings('.item').val(v);
        
        var sec = $(s).val().substring(item_master_fkey.length+1, $(s).val().length); //$(s).val().split("-").pop();
        //alert(sec);
        $(s).parent().parent().siblings().find('.item_qty').val('');
        $(s).parent().parent().siblings().find('.hidden').val(sec);
        //$(s).parent().parent().siblings().find('.item_bal').val(sec);
        
       $.ajax({
           url: livesite+ 'Uniform/getval/'+item_master_fkey,
              statusCode: {
                404: function() {
                  alert( "page not found" );
                }
              },
              success: function (resp) {
                  var item_value = parseInt(resp);
                  $(s).parent().parent().siblings().find('#item_value').val(resp);
                         
                     }
            }); 
//        var sec = $(s).val().split("-");
//        alert(sec[1]);
//        alert(sec[0]);
        loadsums();
    }
    
    
      function removei(s){
          $(s).parent().parent().parent().html("");
          loadsums();
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
            $('#attendanceuploadtable #btn-submit').html('<li class="fa fa-spin fa-spinner "></li> Saving....').attr("disabled","disabled");
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
        //edited by megha on 7_5_19
        $('#date_allocated').datepicker({
            format: 'yyyy-mm-dd',
            autoclose:true, 
            startDate: '-15d',
            endDate: '0d'
        }).on('changeDate', function (selected) {
        var minDate = new Date(selected.date.valueOf());
        $('#emi_start_date').datepicker('setStartDate', minDate);
        });
        $('#emi_start_date').datepicker({
            format: 'yyyy-mm',
            autoclose:true
        })

        $('#emi_end_date').datepicker({
            format: 'yyyy-mm',
            autoclose:true
        })
         //edited by megha on 7_5_19
        $("#out_date").inputmask("yyyy-mm-dd");
    });
</script>



